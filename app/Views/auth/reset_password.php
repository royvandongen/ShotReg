<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= lang('Auth.resetPasswordTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-3"><?= lang('Auth.resetPasswordTitle') ?></h4>

        <?php if (! empty($invalidToken)): ?>
            <div class="alert alert-danger"><?= lang('Auth.invalidResetToken') ?></div>
            <div class="text-center mt-3">
                <a href="/auth/forgot-password"><?= lang('Auth.forgotPassword') ?></a>
            </div>
        <?php else: ?>

            <?php if (! empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= form_open('auth/reset-password/' . esc($token)) ?>
                <div class="mb-3">
                    <label for="password" class="form-label"><?= lang('Auth.newPassword') ?></label>
                    <input type="password" class="form-control" id="password" name="password"
                           required autofocus minlength="8">
                    <div class="form-text"><?= lang('Auth.passwordMinLength') ?></div>
                </div>
                <div class="mb-3">
                    <label for="password_confirm" class="form-label"><?= lang('Auth.confirmPassword') ?></label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.setPassword') ?></button>
            <?= form_close() ?>

            <div class="text-center mt-3">
                <a href="/auth/login"><?= lang('Auth.backToLogin') ?></a>
            </div>

        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
