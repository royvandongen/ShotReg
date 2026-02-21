<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Auth.manage2faTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="bi bi-shield-fill-check text-success" style="font-size: 3rem;"></i>
                </div>
                <h4 class="card-title mb-2"><?= lang('Auth.manage2faTitle') ?></h4>
                <p class="text-muted mb-4"><?= lang('Auth.2faAlreadyEnabled') ?></p>

                <div class="d-grid gap-2">
                    <?= form_open('auth/reset2fa') ?>
                        <button type="submit" class="btn btn-warning"
                                onclick="return confirm('<?= lang('Auth.reset2faConfirm') ?>')">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            <?= lang('Auth.reset2fa') ?>
                        </button>
                    <?= form_close() ?>

                    <?= form_open('auth/disable2fa') ?>
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('<?= lang('Auth.disable2faConfirm') ?>')">
                            <i class="bi bi-shield-x me-1"></i>
                            <?= lang('Auth.disable2fa') ?>
                        </button>
                    <?= form_close() ?>

                    <a href="/dashboard" class="btn btn-secondary">
                        <?= lang('App.cancel') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
