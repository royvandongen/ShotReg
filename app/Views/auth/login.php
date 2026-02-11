<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-3">Log In</h4>

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
                <label for="username" class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= set_value('username') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log In</button>
        <?= form_close() ?>

        <?php if (\App\Libraries\Auth::isRegistrationEnabled()): ?>
        <div class="text-center mt-3">
            <a href="/auth/register">Create an account</a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
