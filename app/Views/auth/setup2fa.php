<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Setup 2FA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Setup Two-Factor Authentication</h4>

                <?php 
                // Only show error from view data, not flash data
                if (isset($error) && ! empty($error)): 
                ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= esc($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <ol class="mb-4">
                    <li>Install an authenticator app (e.g. Google Authenticator, Authy)</li>
                    <li>Scan the QR code below with the app</li>
                    <li>Enter the 6-digit code to verify</li>
                </ol>

                <div class="text-center mb-3">
                    <?= $qrSvg ?>
                </div>

                <div class="mb-4">
                    <label class="form-label">Manual entry key:</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" value="<?= esc($secret) ?>" readonly>
                    </div>
                </div>

                <?= form_open('auth/setup2fa') ?>
                    <div class="mb-3">
                        <label for="totp_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control form-control-lg text-center"
                               id="totp_code" name="totp_code" maxlength="6" pattern="[0-9]{6}"
                               inputmode="numeric" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary">Enable 2FA</button>
                    <a href="/dashboard" class="btn btn-secondary">Cancel</a>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
