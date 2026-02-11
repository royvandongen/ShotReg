<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Verify 2FA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-3">Two-Factor Authentication</h4>
        <p class="text-muted">Enter the 6-digit code from your authenticator app.</p>

        <?php if (! empty($error)): ?>
            <div class="alert alert-danger"><?= esc($error) ?></div>
        <?php endif; ?>

        <?= form_open('auth/verify2fa') ?>
            <div class="mb-3">
                <label for="totp_code" class="form-label">Authentication Code</label>
                <input type="text" class="form-control form-control-lg text-center"
                       id="totp_code" name="totp_code" maxlength="6" pattern="[0-9]{6}"
                       inputmode="numeric" autocomplete="one-time-code" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        <?= form_close() ?>

        <div class="text-center mt-3">
            <a href="/auth/login">Back to login</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
