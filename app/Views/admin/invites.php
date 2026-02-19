<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Admin.invitesTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4"><?= lang('Admin.invitesTitle') ?></h3>

<?php
$inviteLink = session()->getFlashdata('invite_link');
if ($inviteLink): ?>
<div class="alert alert-success alert-dismissible fade show">
    <div class="mb-2"><?= session()->getFlashdata('success') ?></div>
    <div class="input-group">
        <input type="text" class="form-control font-monospace small" id="inviteLinkBox"
               value="<?= esc($inviteLink) ?>" readonly>
        <button class="btn btn-outline-secondary" type="button" onclick="copyInviteLink()">
            <i class="bi bi-clipboard"></i> <?= lang('Admin.copyLink') ?>
        </button>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php elseif (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><strong><?= lang('Admin.sendInvite') ?></strong></div>
    <div class="card-body">
        <?= form_open('/admin/invites/send', ['class' => 'row g-2']) ?>
            <div class="col">
                <input type="email" class="form-control" name="invite_email"
                       placeholder="<?= lang('Admin.inviteEmailPlaceholder') ?>" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-envelope-plus"></i> <?= lang('Admin.sendInvite') ?>
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>

<?php
// Group invites by sender
$bySender = [];
$system   = [];
foreach ($invites as $inv) {
    if ($inv['invited_by']) {
        $bySender[$inv['invited_by']]['username'] = $inv['invited_by_username'] ?? '—';
        $bySender[$inv['invited_by']]['invites'][] = $inv;
    } else {
        $system[] = $inv;
    }
}
$now = new DateTime();
?>

<?php if (empty($invites)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-4"><?= lang('Admin.noInvites') ?></div>
</div>
<?php else: ?>

<?php foreach ($bySender as $senderId => $senderData): ?>
<?php
$stats    = $senderStats[$senderId] ?? [];
$override = $stats['override'] ?? null;
$global   = $stats['global_limit'] ?? 5;
$limit    = ($override !== null && (int)$override > 0) ? (int)$override
          : (($override === '0') ? null : $global);
$used     = count($senderData['invites']);
?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <strong><i class="bi bi-person"></i> <?= esc($senderData['username']) ?></strong>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <small class="text-muted">
                <?= $used ?> <?= lang('Admin.inviteUsed') ?>
                <?php if ($limit !== null): ?>
                    <?= lang('Admin.inviteOf') ?> <?= $limit ?>
                <?php else: ?>
                    (<?= lang('Admin.unlimited') ?>)
                <?php endif; ?>
                <?php if ($override !== null): ?>
                    &mdash; <em><?= lang('Admin.inviteLimit') ?>: <?= lang('Admin.inviteLimit') ?> override</em>
                <?php else: ?>
                    &mdash; <em><?= lang('Admin.globalLimit') ?></em>
                <?php endif; ?>
            </small>

            <?= form_open('/admin/invites/set-limit/' . $senderId, ['class' => 'd-inline-flex gap-1']) ?>
                <input type="number" name="invite_limit" class="form-control form-control-sm"
                       style="width:80px;"
                       placeholder="<?= $limit ?? '∞' ?>"
                       min="0" value="<?= $override !== null ? esc($override) : '' ?>">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><?= lang('Admin.setLimit') ?></button>
            <?= form_close() ?>

            <?= form_open('/admin/invites/revoke/' . $senderId, ['class' => 'd-inline']) ?>
                <button type="submit" class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('<?= lang('Admin.revokeConfirm', [esc($senderData['username'])]) ?>')">
                    <i class="bi bi-x-circle"></i> <?= lang('Admin.revokeInvites') ?>
                </button>
            <?= form_close() ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('Admin.inviteRecipient') ?></th>
                    <th><?= lang('Admin.inviteStatus') ?></th>
                    <th><?= lang('Admin.inviteCreatedAt') ?></th>
                    <th><?= lang('Admin.inviteExpires') ?></th>
                    <th><?= lang('Admin.inviteLink') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($senderData['invites'] as $inv): ?>
                <?php
                $expires = new DateTime($inv['expires_at']);
                $isPending = empty($inv['used_at']) && $expires > $now;
                $isUsed    = ! empty($inv['used_at']);
                $isExpired = empty($inv['used_at']) && $expires <= $now;
                ?>
                <tr>
                    <td><?= esc($inv['email']) ?></td>
                    <td>
                        <?php if ($isPending): ?>
                            <span class="badge bg-warning text-dark"><?= lang('Admin.inviteStatusPending') ?></span>
                        <?php elseif ($isUsed): ?>
                            <span class="badge bg-success"><?= lang('Admin.inviteStatusUsed') ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= lang('Admin.inviteStatusExpired') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><small class="text-muted"><?= date('d M Y H:i', strtotime($inv['created_at'])) ?></small></td>
                    <td><small class="text-muted"><?= date('d M Y H:i', strtotime($inv['expires_at'])) ?></small></td>
                    <td>
                        <?php if ($isPending): ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="copyText('<?= esc(rtrim(config('App')->baseURL, '/')) ?>/invite/<?= esc($inv['token']) ?>')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<?php if (! empty($system)): ?>
<div class="card mb-3">
    <div class="card-header"><strong><?= lang('Admin.inviteSentBy') ?>: System</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('Admin.inviteRecipient') ?></th>
                    <th><?= lang('Admin.inviteStatus') ?></th>
                    <th><?= lang('Admin.inviteCreatedAt') ?></th>
                    <th><?= lang('Admin.inviteExpires') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($system as $inv): ?>
                <?php
                $expires = new DateTime($inv['expires_at']);
                $isPending = empty($inv['used_at']) && $expires > $now;
                $isUsed    = ! empty($inv['used_at']);
                ?>
                <tr>
                    <td><?= esc($inv['email']) ?></td>
                    <td>
                        <?php if ($isPending): ?>
                            <span class="badge bg-warning text-dark"><?= lang('Admin.inviteStatusPending') ?></span>
                        <?php elseif ($isUsed): ?>
                            <span class="badge bg-success"><?= lang('Admin.inviteStatusUsed') ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= lang('Admin.inviteStatusExpired') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><small class="text-muted"><?= date('d M Y H:i', strtotime($inv['created_at'])) ?></small></td>
                    <td><small class="text-muted"><?= date('d M Y H:i', strtotime($inv['expires_at'])) ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?= $this->section('scripts') ?>
<script>
function copyInviteLink() {
    var box = document.getElementById('inviteLinkBox');
    if (box) { box.select(); document.execCommand('copy'); }
}
function copyText(text) {
    navigator.clipboard.writeText(text).catch(function() {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    });
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
