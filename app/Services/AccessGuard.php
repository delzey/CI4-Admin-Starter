<?php

namespace App\Services;

use CodeIgniter\I18n\Time;
use CodeIgniter\Cache\CacheInterface;
use Psr\Log\LoggerInterface;
use Config\AccessGuard as AccessGuardConfig;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AccessGuard
{
    public function __construct(
        protected CacheInterface $cache,
        protected LoggerInterface $logger,
        protected AccessGuardConfig $config
    ) {}

    /**
     * Main entry point: call this whenever access is denied.
     */
    public function onDenied(?User $user, string $contextSlug): void
    {
        if (! $user) {
            return; // guest, nothing to message internally
        }

        $this->sendOneTimeSystemMessage($user->id, $contextSlug);
        $this->trackDeniedAttempt($user->id, $contextSlug);
    }

    /**
     * Build a cache-safe key (no reserved characters).
     */
    protected function makeCacheKey(string $type, int $userId, ?string $contextSlug = null): string
    {
        $suffix = $contextSlug ?? 'global';

        // Replace anything not alnum, underscore or dash with underscore
        $suffix = preg_replace('/[^A-Za-z0-9_\-]/', '_', $suffix);

        return "accessguard_{$type}_{$userId}_{$suffix}";
    }

    protected function sendOneTimeSystemMessage(int $userId, string $contextSlug): void
    {
        $key = $this->makeCacheKey('msg', $userId, $contextSlug);

        // If key exists, they've already been notified recently
        if ($this->cache->get($key)) {
            return;
        }

        $subject = 'Access Denied Notification';
        $body    = "You attempted to access a restricted area ({$contextSlug}). "
                 . "If you believe this is an error, please contact your administrator.";

        // Hook into your existing messaging system here.
        // Adjust to match your actual method signature.
        try {
            if (function_exists('service')) {
                $messaging = service('messaging');
                if ($messaging) {
                    // Example signature; adjust to your actual method
                    $messaging->sendSystemMessage($userId, $subject, $body);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning(
                "AccessGuard: failed to send system message to user {$userId}: " . $e->getMessage()
            );
        }

        $this->cache->save($key, 1, $this->config->messageCooldown);
    }

    protected function trackDeniedAttempt(int $userId, string $contextSlug): void
    {
        // For counting we don’t really need per-context, but we can include it
        $key  = $this->makeCacheKey('count', $userId, null);
        $now  = Time::now()->getTimestamp();
        $data = $this->cache->get($key);

        if (! is_array($data) || ! isset($data['first'], $data['count'])) {
            $data = [
                'first' => $now,
                'count' => 0,
            ];
        }

        // If window expired, reset
        if (($now - $data['first']) > $this->config->windowSeconds) {
            $data['first'] = $now;
            $data['count'] = 0;
        }

        $data['count']++;

        $this->cache->save($key, $data, $this->config->windowSeconds);

        $this->maybeEscalate($userId, $contextSlug, $data, $key);
    }

    protected function maybeEscalate(int $userId, string $contextSlug, array $data, string $counterKey): void
    {
        $count    = $data['count'];
        $username = $this->getUsernameForLog($userId);

        if ($count === $this->config->warnThreshold) {
            $msg = "AccessGuard: {$username} has {$count} denied access attempts "
                . "within the configured window. Context: {$contextSlug}";

            $this->logger->warning($msg);

            $this->notifyAdmins(
                'Suspicious access pattern detected',
                $msg
            );
        }

        if ($count >= $this->config->lockThreshold) {
            $msg = "AccessGuard: {$username} exceeded lock threshold ({$count} attempts). "
                . "Context: {$contextSlug}. Forcing logout.";

            $this->logger->error($msg);

            try {
                if (function_exists('auth') && auth()->loggedIn()) {
                    auth()->logout();
                }
            } catch (\Throwable $e) {
                $this->logger->error('AccessGuard: failed to force logout: ' . $e->getMessage());
            }

            $this->notifyAdmins(
                'User forced logout due to repeated access violations',
                $msg
            );

            $this->cache->delete($counterKey);
        }
    }

    /**
     * Send a security alert to admins via your messaging system.
     */
    protected function notifyAdmins(string $subject, string $body): void
    {
        try {
            if (function_exists('service')) {
                $messaging = service('messageService');
                if ($messaging && method_exists($messaging, 'sendSystemMessageToAdmins')) {
                    $messaging->sendSystemMessageToAdmins($subject, $body);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('AccessGuard: failed to notify admins: ' . $e->getMessage());
        }
    }

    /**
     * Get the username for the messaging system.
     */
    protected function getUsernameForLog(int $userId): string
    {
        try {
            $userModel = new UserModel();
            $user      = $userModel->find($userId);

            if ($user && ! empty($user->username)) {
                return (string) $user->username;
            }
        } catch (\Throwable $e) {
            $this->logger->warning('AccessGuard: failed to fetch username: ' . $e->getMessage());
        }

        return "User #{$userId}";
    }
}
