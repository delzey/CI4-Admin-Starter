<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fas fa-paper-plane me-2"></i> Outbox
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-paper-plane me-2"></i> Outbox</span>
        <a href="<?= route_to('messages.compose') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-pencil-alt me-1"></i> Compose
        </a>
    </div>

    <div class="card-body p-0">
        <?php if (session('success')): ?>
            <div class="alert alert-success m-3"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <?php if (empty($messages)): ?>
            <div class="text-muted text-center py-4">
                No sent messages.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Sent At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td><?= esc($m['subject'] ?: '(no subject)') ?></td>
                            <td><?= formatDateBySetting($m['sent_at'], 'm/d/Y H:i') ?></td>
                            <td class="text-end">
                                <a href="<?= route_to('messages.show', $m['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>

                                <form action="<?= route_to('messages.delete', $m['id']) ?>"
                                      method="post"
                                      class="d-inline"
                                      onsubmit="return confirm('Remove from Outbox?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
