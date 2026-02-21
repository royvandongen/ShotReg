<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Admin.systemSettings') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4"><?= lang('Admin.systemSettings') ?></h3>

<?php if ($diskTotal !== null && $diskFree !== null): ?>
<?php
    $__diskUsed     = $diskTotal - $diskFree;
    $__diskPct      = round(($__diskUsed / $diskTotal) * 100, 1);
    $__diskBarClass = $__diskPct >= 90 ? 'bg-danger' : ($__diskPct >= 75 ? 'bg-warning' : 'bg-success');
    $__freeGb       = round($diskFree  / 1073741824, 1);
    $__totalGb      = round($diskTotal / 1073741824, 1);
    $__usedGb       = round($__diskUsed / 1073741824, 1);
?>
<div class="card mb-4">
    <div class="card-header"><strong><i class="bi bi-hdd"></i> <?= lang('Admin.diskSpace') ?></strong></div>
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2 small">
            <span><?= lang('Admin.diskUsed') ?>: <strong><?= $__usedGb ?> GB</strong></span>
            <span><?= lang('Admin.diskFree') ?>: <strong><?= $__freeGb ?> GB</strong> <?= lang('Admin.diskOf') ?> <?= $__totalGb ?> GB</span>
        </div>
        <div class="progress" style="height:22px;">
            <div class="progress-bar <?= $__diskBarClass ?>"
                 role="progressbar"
                 style="width:<?= $__diskPct ?>%"
                 aria-valuenow="<?= $__diskPct ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
                <?= $__diskPct ?>%
            </div>
        </div>
        <?php if ($__diskPct >= 90): ?>
        <p class="text-danger small mt-2 mb-0">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= lang('Admin.diskSpaceWarning') ?>
        </p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
    $__anyPermIssue = false;
    foreach ($writableDirs as $__d) {
        if (!$__d['exists'] || !$__d['readable'] || !$__d['writable']) {
            $__anyPermIssue = true;
            break;
        }
    }
?>
<div class="card mb-4">
    <div class="card-header"><strong><i class="bi bi-folder-check"></i> <?= lang('Admin.writablePerms') ?></strong></div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('Admin.writablePath') ?></th>
                    <th class="text-center"><?= lang('Admin.writableExists') ?></th>
                    <th class="text-center"><?= lang('Admin.writableReadable') ?></th>
                    <th class="text-center"><?= lang('Admin.writableWritable') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($writableDirs as $__d): ?>
                <tr class="<?= (!$__d['exists'] || !$__d['readable'] || !$__d['writable']) ? 'table-danger' : '' ?>">
                    <td class="font-monospace small"><?= esc($__d['path']) ?></td>
                    <td class="text-center"><?= $__d['exists']   ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?></td>
                    <td class="text-center"><?= $__d['readable'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?></td>
                    <td class="text-center"><?= $__d['writable'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($__anyPermIssue): ?>
        <p class="text-danger small m-3 mb-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= lang('Admin.writablePermsWarning') ?>
        </p>
        <?php else: ?>
        <p class="text-success small m-3 mb-2">
            <i class="bi bi-check-circle-fill"></i>
            <?= lang('Admin.writablePermsOk') ?>
        </p>
        <?php endif; ?>
    </div>
</div>

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

            <hr>
            <h6 class="mb-3"><?= lang('Admin.invitesSection') ?></h6>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="invites_enabled"
                       name="invites_enabled" value="1"
                       <?= $invitesEnabled === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="invites_enabled">
                    <?= lang('Admin.enableInvites') ?>
                </label>
            </div>
            <p class="text-muted small mb-4">
                <?= lang('Admin.enableInvitesHelp') ?>
            </p>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="user_invites_enabled"
                       name="user_invites_enabled" value="1"
                       <?= $userInvitesEnabled === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="user_invites_enabled">
                    <?= lang('Admin.enableUserInvites') ?>
                </label>
            </div>
            <p class="text-muted small mb-4">
                <?= lang('Admin.enableUserInvitesHelp') ?>
            </p>

            <div class="mb-4">
                <label for="user_invite_limit" class="form-label"><?= lang('Admin.userInviteLimit') ?></label>
                <input type="number" class="form-control" id="user_invite_limit"
                       name="user_invite_limit" value="<?= esc($userInviteLimit) ?>"
                       min="0" style="max-width: 120px;">
                <div class="form-text"><?= lang('Admin.userInviteLimitHelp') ?></div>
            </div>

            <hr>
            <h6 class="mb-3"><?= lang('Admin.passwordResetSection') ?></h6>

            <div class="mb-4">
                <label for="password_reset_expiry_minutes" class="form-label">
                    <?= lang('Admin.resetExpiryMinutes') ?>
                </label>
                <div class="input-group" style="max-width: 180px;">
                    <input type="number" class="form-control" id="password_reset_expiry_minutes"
                           name="password_reset_expiry_minutes"
                           value="<?= esc($resetExpiryMinutes) ?>" min="1">
                    <span class="input-group-text"><?= lang('Admin.minutes') ?></span>
                </div>
            </div>

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
