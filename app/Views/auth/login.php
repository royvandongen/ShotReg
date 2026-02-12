<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= lang('Auth.login') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-3"><?= lang('Auth.loginTitle') ?></h4>

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

        <?= form_open('auth/login') ?>
            <div class="mb-3">
                <label for="username" class="form-label"><?= lang('Auth.usernameOrEmail') ?></label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= set_value('username') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><?= lang('Auth.password') ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.login') ?></button>
        <?= form_close() ?>

        <?php if (\App\Libraries\Auth::isRegistrationEnabled()): ?>
        <div class="text-center mt-3">
            <a href="/auth/register"><?= lang('Auth.createAccount') ?></a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
