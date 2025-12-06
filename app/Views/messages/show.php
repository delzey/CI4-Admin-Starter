<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fas fa-envelope-open-text me-2"></i> Message
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong><?= esc($message['subject'] ?: '(no subject)') ?></strong><br>
            <small class="text-muted">
                From: <?= esc($message['from_username'] ?? 'System') ?> ·
                Sent: <?= esc($message['sent_at']) ?>
            </small>
        </div>

        <div>
            <a href="<?= route_to('messages.inbox') ?>" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

            <form action="<?= route_to('messages.delete', $message['id']) ?>"
                  method="post"
                  class="d-inline"
                  onsubmit="return confirm('Delete this message?');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <?php if (!empty($message['recipients'])): ?>
            <p class="mb-3 small text-muted">
                To:
                <?php foreach ($message['recipients'] as $idx => $r): ?>
                    <?= $idx ? ', ' : '' ?><?= esc($r['username']) ?>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>

        <div class="border rounded p-3 bg-light">
            <?= nl2br(esc($message['body'])) ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
