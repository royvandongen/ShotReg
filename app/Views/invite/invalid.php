<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= lang('Invite.invalidTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body text-center">
        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
        <h4 class="card-title mt-3"><?= lang('Invite.invalidTitle') ?></h4>
        <p class="text-muted"><?= lang('Invite.invalidMessage') ?></p>
        <a href="/auth/login" class="btn btn-primary"><?= lang('Auth.backToLogin') ?></a>
    </div>
</div>
<?= $this->endSection() ?>
