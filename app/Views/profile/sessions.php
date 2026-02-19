<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Profile.sessionsTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><?= lang('Profile.sessionsTitle') ?></h3>
    <a href="/profile" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> <?= lang('Profile.title') ?>
    </a>
</div>

<p class="text-muted"><?= lang('Profile.sessionsHelp') ?></p>

<?php if (empty($tokens)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> <?= lang('Profile.noActiveSessions') ?>
    </div>
<?php else: ?>
    <div class="card mb-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= lang('Profile.device') ?></th>
                        <th><?= lang('Profile.ipAddress') ?></th>
                        <th><?= lang('Profile.lastSeen') ?></th>
                        <th><?= lang('Profile.expires') ?></th>
                        <th class="text-center"><?= lang('App.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tokens as $token): ?>
                    <tr>
                        <td>
                            <i class="bi bi-<?= strpos($token['device_name'] ?? '', 'iPhone') !== false || strpos($token['device_name'] ?? '', 'Android phone') !== false ? 'phone' : (strpos($token['device_name'] ?? '', 'iPad') !== false || strpos($token['device_name'] ?? '', 'Android tablet') !== false ? 'tablet' : 'laptop') ?>"></i>
                            <?= esc($token['device_name'] ?? lang('Profile.unknownDevice')) ?>
                            <?php if ($token['selector'] === $currentSelector): ?>
                                <span class="badge bg-success ms-1"><?= lang('Profile.thisDevice') ?></span>
                            <?php endif; ?>
                            <?php if ($token['totp_trusted']): ?>
                                <span class="badge bg-secondary ms-1" title="<?= lang('Profile.totpTrusted') ?>">
                                    <i class="bi bi-shield-check"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= esc($token['ip_address'] ?? '—') ?></small>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $token['last_used_at'] ? date('d M Y, H:i', strtotime($token['last_used_at'])) : '—' ?>
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= date('d M Y', strtotime($token['expires_at'])) ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <?= form_open('/profile/sessions/revoke/' . esc($token['selector'])) ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                        title="<?= lang('Profile.signOutDevice') ?>"
                                        onclick="return confirm('<?= lang('Profile.signOutDeviceConfirm') ?>')">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                            <?= form_close() ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $otherCount = count(array_filter($tokens, fn($t) => $t['selector'] !== $currentSelector)); ?>
    <?php if ($otherCount > 0): ?>
    <div class="d-flex justify-content-end">
        <?= form_open('/profile/sessions/revoke-others') ?>
            <button type="submit" class="btn btn-outline-warning"
                    onclick="return confirm('<?= lang('Profile.signOutOtherSessionsConfirm') ?>')">
                <i class="bi bi-shield-exclamation"></i>
                <?= lang('Profile.signOutOtherSessions') ?>
            </button>
        <?= form_close() ?>
    </div>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
