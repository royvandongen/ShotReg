<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= lang('Auth.forgotPasswordTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-3"><?= lang('Auth.forgotPasswordTitle') ?></h4>
        <p class="text-muted small mb-3"><?= lang('Auth.forgotPasswordHelp') ?></p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= form_open('auth/forgot-password') ?>
            <div class="mb-3">
                <label for="email" class="form-label"><?= lang('App.email') ?></label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= set_value('email') ?>" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.sendResetLink') ?></button>
        <?= form_close() ?>

        <div class="text-center mt-3">
            <a href="/auth/login"><?= lang('Auth.backToLogin') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
