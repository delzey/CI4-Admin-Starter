<?php

namespace App\Services;

use App\Models\MessageModel;
use App\Models\MessageRecipientModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\User;

class MessageService
{
    protected MessageModel $messages;
    protected MessageRecipientModel $recipients;

    public function __construct(
        ?MessageModel $messages = null,
        ?MessageRecipientModel $recipients = null
    ) {
        $this->messages   = $messages   ?? new MessageModel();
        $this->recipients = $recipients ?? new MessageRecipientModel();
    }

    /**
     * Send a message from a user to one or many users.
     */
    public function send(int $fromUser, int|array $toUsers, string $subject, string $body): int
    {
        if (! is_array($toUsers)) {
            $toUsers = [$toUsers];
        }

        $now = Time::now();

        // Base message
        $msgId = $this->messages->insert([
            'subject' => $subject,
            'body'    => $body,
            'sent_by' => $fromUser,
            'sent_at' => $now,
        ], true);

        // Outbox record for sender
        $this->recipients->insert([
            'message_id' => $msgId,
            'user_id'    => $fromUser,
            'folder'     => 'outbox',
            'is_read'    => 1,
            'is_deleted' => 0,
            'created_at' => $now,
        ]);

        // Inbox records for recipients
        foreach ($toUsers as $userId) {
            $this->recipients->insert([
                'message_id' => $msgId,
                'user_id'    => $userId,
                'folder'     => 'inbox',
                'is_read'    => 0,
                'is_deleted' => 0,
                'created_at' => $now,
            ]);
        }

        return $msgId;
    }

    /**
     * System-generated message (no explicit sender).
     */
    public function systemMessage(int|array $toUsers, string $subject, string $body): int
    {
        if (! is_array($toUsers)) {
            $toUsers = [$toUsers];
        }

        $now   = Time::now();
        $msgId = $this->messages->insert([
            'subject' => $subject,
            'body'    => $body,
            'sent_by' => null,   // NULL = system (satisfies FK)
            'sent_at' => $now,
        ], true);

        foreach ($toUsers as $userId) {
            $this->recipients->insert([
                'message_id' => $msgId,
                'user_id'    => $userId,
                'folder'     => 'system',
                'is_read'    => 0,
                'is_deleted' => 0,
                'created_at' => $now,
            ]);
        }

        return $msgId;
    }

    /**
     * Send a system message to all configured security/admin users.
     *
     * Admin user IDs are defined in Config\AccessGuard::$adminUserIds.
     */
    public function sendSystemMessageToAdmins(string $subject, string $body): void
    {
        $config    = config('AccessGuard');
        $adminIds  = $config->adminUserIds ?? [];

        if (empty($adminIds)) {
            return; // nothing to do
        }

        foreach ($adminIds as $userId) {
            $this->systemMessage((int) $userId, $subject, $body);
        }
    }

    /**
     * Inbox listing for a user (not deleted).
     */
    public function inbox(int $userId): array
    {
        return $this->recipients
            ->select('message_recipients.*, messages.subject, messages.sent_by, messages.sent_at, u.username AS from_username')
            ->join('messages', 'messages.id = message_recipients.message_id')
            ->join('users u', 'u.id = messages.sent_by', 'left')
            ->where('message_recipients.user_id', $userId)
            ->whereIn('message_recipients.folder', ['inbox', 'system'])
            ->where('message_recipients.is_deleted', 0)
            ->orderBy('messages.sent_at', 'DESC')
            ->findAll();
    }

    /**
     * Outbox listing for a user (not deleted).
     */
    public function outbox(int $userId): array
    {
        // We’ll show subject + sent_at here; recipients can be resolved in detail view if needed.
        return $this->recipients
            ->select('message_recipients.*, messages.subject, messages.sent_by, messages.sent_at')
            ->join('messages', 'messages.id = message_recipients.message_id')
            ->where('message_recipients.user_id', $userId)
            ->where('message_recipients.folder', 'outbox')
            ->where('message_recipients.is_deleted', 0)
            ->orderBy('messages.sent_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a single mailbox row (Inbox/Outbox entry) + message + sender/recipients.
     */
    public function getRecipientRow(int $recipientId, int $userId): ?array
    {
        $row = $this->recipients
            ->select('message_recipients.*, messages.subject, messages.body, messages.sent_by, messages.sent_at, u.username AS from_username')
            ->join('messages', 'messages.id = message_recipients.message_id')
            ->join('users u', 'u.id = messages.sent_by', 'left')
            ->where('message_recipients.id', $recipientId)
            ->where('message_recipients.user_id', $userId)
            ->first();

        if (! $row) {
            return null;
        }

        // Add recipients list for this message (for display in "show" view)
        $row['recipients'] = $this->recipients
            ->select('message_recipients.user_id, users.username')
            ->join('users', 'users.id = message_recipients.user_id')
            ->where('message_recipients.message_id', $row['message_id'])
            ->where('message_recipients.folder', 'inbox')
            ->findAll();

        return $row;
    }

    /**
     * Soft-delete a mailbox entry (does not affect the other side).
     */
    public function deleteForUser(int $recipientId, int $userId): bool
    {
        return $this->recipients
            ->where('id', $recipientId)
            ->where('user_id', $userId)
            ->set(['is_deleted' => 1])
            ->update();
    }

    /**
     * Mark a message entry as read.
     */
    public function markRead(int $recipientId, int $userId): bool
    {
        return $this->recipients
            ->where('id', $recipientId)
            ->where('user_id', $userId)
            ->set(['is_read' => 1])
            ->update();
    }

    /**
     * Count unread messages in inbox + system for badge in navbar.
     */
    public function unreadCount(int $userId): int
    {
        return $this->recipients
            ->where('user_id', $userId)
            ->whereIn('folder', ['inbox', 'system'])
            ->where('is_read', 0)
            ->where('is_deleted', 0)
            ->countAllResults();
    }

    public function getUnreadCount(int $userId): int
    {
        $recipients = new MessageRecipientModel();

        return $recipients
            ->where('user_id', $userId)
            ->whereIn('folder', ['inbox', 'system'])
            ->where('is_deleted', 0)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function getInboxPreview(int $userId, int $limit = 5): array
    {
        $recipients = new MessageRecipientModel();

        return $recipients->select("
                message_recipients.is_read,
                messages.id,
                messages.subject,
                messages.sent_at,
                users.username AS sender
            ")
            ->join('messages', 'messages.id = message_recipients.message_id')
            ->join('users', 'users.id = messages.sent_by', 'left')
            ->where('message_recipients.user_id', $userId)
            ->whereIn('message_recipients.folder', ['inbox', 'system']) // 👈 add system
            ->where('message_recipients.is_deleted', 0)
            ->orderBy('messages.sent_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
