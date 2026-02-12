<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= lang('Auth.register') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-3"><?= lang('Auth.registerTitle') ?></h4>

        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= form_open('auth/register') ?>
            <div class="mb-3">
                <label for="username" class="form-label"><?= lang('Auth.username') ?></label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= set_value('username') ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><?= lang('App.email') ?></label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= set_value('email') ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><?= lang('Auth.password') ?></label>
                <input type="password" class="form-control" id="password" name="password"
                       required minlength="8">
                <div class="form-text"><?= lang('Auth.passwordMinLength') ?></div>
            </div>
            <div class="mb-3">
                <label for="password_confirm" class="form-label"><?= lang('Auth.confirmPassword') ?></label>
                <input type="password" class="form-control" id="password_confirm"
                       name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.registerTitle') ?></button>
        <?= form_close() ?>

        <div class="text-center mt-3">
            <a href="/auth/login"><?= lang('Auth.alreadyHaveAccount') ?></a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
