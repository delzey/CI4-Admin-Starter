<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fas fa-pencil-alt me-2"></i> Compose Message
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <strong>Compose Message</strong>
            </div>
            <div class="card-body">

                <?php if ($errors = session('errors')): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <?= esc($e) ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="<?= route_to('messages.send') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">To (User ID)</label>
                        <input type="number" name="to" class="form-control" value="<?= old('to') ?>" required>
                        <small class="text-muted">We’ll swap this to a user dropdown later.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" value="<?= old('subject') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="body" rows="6" class="form-control" required><?= old('body') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="<?= route_to('messages.inbox') ?>" class="btn btn-outline-secondary me-2">
                            Cancel
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
