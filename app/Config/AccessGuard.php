<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AccessGuard extends BaseConfig
{
    /**
     * How often (in seconds) the same user/context
     * can receive a system message.
     */
    public int $messageCooldown = 3600; // 1 hour

    /**
     * Sliding window (in seconds) for counting denied attempts.
     */
    public int $windowSeconds = 900; // 15 minutes

    /**
     * When count >= warnThreshold, we just log & maybe notify admins.
     */
    public int $warnThreshold = 5;

    /**
     * When count >= lockThreshold, we escalate (e.g., force logout).
     */
    public int $lockThreshold = 10; // This should be the limit

    /**
     * User IDs that should receive security/admin alerts
     * when suspicious access patterns are detected.
     */
    public array $adminUserIds = [1]; // adjust as you like to send to the ID's you want
    // public array $adminUserIds = [1, 2, 5];
}
