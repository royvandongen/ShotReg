<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Admin.systemSettings') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4"><?= lang('Admin.systemSettings') ?></h3>

<div class="card mb-4">
    <div class="card-header"><strong><?= lang('Admin.general') ?></strong></div>
    <div class="card-body">
        <?= form_open('/admin/settings') ?>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="registration_enabled"
                       name="registration_enabled" value="1"
                       <?= $registrationEnabled !== '0' ? 'checked' : '' ?>>
                <label class="form-check-label" for="registration_enabled">
                    <?= lang('Admin.allowRegistrations') ?>
                </label>
            </div>
            <p class="text-muted small mb-4">
                <?= lang('Admin.registrationHelp') ?>
            </p>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="force_2fa"
                       name="force_2fa" value="1"
                       <?= $force2fa === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="force_2fa">
                    <?= lang('Admin.require2fa') ?>
                </label>
            </div>
            <p class="text-muted small mb-4">
                <?= lang('Admin.require2faHelp') ?>
            </p>

            <button type="submit" class="btn btn-primary"><?= lang('Admin.saveSettings') ?></button>
        <?= form_close() ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><strong><?= lang('Admin.defaultLaneTypes') ?></strong></div>
    <div class="card-body">
        <p class="text-muted small"><?= lang('Admin.defaultLaneTypesHelp') ?></p>
        <?php if (! empty($defaultLaneTypes)): ?>
        <div class="mb-3">
            <?php foreach ($defaultLaneTypes as $i => $lt): ?>
                <span class="badge bg-secondary me-1 mb-1">
                    <?= esc($lt['label']) ?>
                    <?= form_open('/admin/defaults/delete', ['class' => 'd-inline']) ?>
                        <input type="hidden" name="type" value="lane_type">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;"
                                onclick="return confirm('<?= lang('Admin.removeDefaultConfirm') ?>')"></button>
                    <?= form_close() ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted small mb-3"><?= lang('Admin.noDefaultLaneTypes') ?></p>
        <?php endif; ?>

        <?= form_open('/admin/defaults/add', ['class' => 'input-group']) ?>
            <input type="hidden" name="type" value="lane_type">
            <input type="text" class="form-control" name="label" placeholder="<?= lang('Admin.laneTypePlaceholder') ?>" required>
            <button type="submit" class="btn btn-outline-primary"><?= lang('App.add') ?></button>
        <?= form_close() ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong><?= lang('Admin.defaultSightings') ?></strong></div>
    <div class="card-body">
        <p class="text-muted small"><?= lang('Admin.defaultSightingsHelp') ?></p>
        <?php if (! empty($defaultSightings)): ?>
        <div class="mb-3">
            <?php foreach ($defaultSightings as $i => $s): ?>
                <span class="badge bg-secondary me-1 mb-1">
                    <?= esc($s['label']) ?>
                    <?= form_open('/admin/defaults/delete', ['class' => 'd-inline']) ?>
                        <input type="hidden" name="type" value="sighting">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;"
                                onclick="return confirm('<?= lang('Admin.removeDefaultConfirm') ?>')"></button>
                    <?= form_close() ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted small mb-3"><?= lang('Admin.noDefaultSightings') ?></p>
        <?php endif; ?>

        <?= form_open('/admin/defaults/add', ['class' => 'input-group']) ?>
            <input type="hidden" name="type" value="sighting">
            <input type="text" class="form-control" name="label" placeholder="<?= lang('Admin.sightingPlaceholder') ?>" required>
            <button type="submit" class="btn btn-outline-primary"><?= lang('App.add') ?></button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
