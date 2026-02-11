<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>System Settings<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4">System Settings</h3>

<div class="card mb-4">
    <div class="card-header"><strong>General</strong></div>
    <div class="card-body">
        <?= form_open('/admin/settings') ?>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="registration_enabled"
                       name="registration_enabled" value="1"
                       <?= $registrationEnabled !== '0' ? 'checked' : '' ?>>
                <label class="form-check-label" for="registration_enabled">
                    Allow new user registrations
                </label>
            </div>
            <p class="text-muted small mb-4">
                When disabled, only existing users can log in. New accounts can still be created via the CLI.
            </p>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="force_2fa"
                       name="force_2fa" value="1"
                       <?= $force2fa === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="force_2fa">
                    Require two-factor authentication for all users
                </label>
            </div>
            <p class="text-muted small mb-4">
                When enabled, users without 2FA will be redirected to set it up before they can access the application.
            </p>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        <?= form_close() ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><strong>Default Lane Types for New Users</strong></div>
    <div class="card-body">
        <p class="text-muted small">These lane types will be automatically added when a new user registers.</p>
        <?php if (! empty($defaultLaneTypes)): ?>
        <div class="mb-3">
            <?php foreach ($defaultLaneTypes as $i => $lt): ?>
                <span class="badge bg-secondary me-1 mb-1">
                    <?= esc($lt['label']) ?>
                    <?= form_open('/admin/defaults/delete', ['class' => 'd-inline']) ?>
                        <input type="hidden" name="type" value="lane_type">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;"
                                onclick="return confirm('Remove this default?')"></button>
                    <?= form_close() ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted small mb-3">No defaults configured. The built-in defaults (25m, 50m, 100m) will be used.</p>
        <?php endif; ?>

        <?= form_open('/admin/defaults/add', ['class' => 'input-group']) ?>
            <input type="hidden" name="type" value="lane_type">
            <input type="text" class="form-control" name="label" placeholder="e.g. 25m" required>
            <button type="submit" class="btn btn-outline-primary">Add</button>
        <?= form_close() ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Default Sighting Options for New Users</strong></div>
    <div class="card-body">
        <p class="text-muted small">These sighting options will be automatically added when a new user registers.</p>
        <?php if (! empty($defaultSightings)): ?>
        <div class="mb-3">
            <?php foreach ($defaultSightings as $i => $s): ?>
                <span class="badge bg-secondary me-1 mb-1">
                    <?= esc($s['label']) ?>
                    <?= form_open('/admin/defaults/delete', ['class' => 'd-inline']) ?>
                        <input type="hidden" name="type" value="sighting">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;"
                                onclick="return confirm('Remove this default?')"></button>
                    <?= form_close() ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted small mb-3">No defaults configured. The built-in defaults (Front Sight, Aperture Sight, Scope) will be used.</p>
        <?php endif; ?>

        <?= form_open('/admin/defaults/add', ['class' => 'input-group']) ?>
            <input type="hidden" name="type" value="sighting">
            <input type="text" class="form-control" name="label" placeholder="e.g. Scope" required>
            <button type="submit" class="btn btn-outline-primary">Add</button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
