<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Admin.usersTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><?= lang('Admin.usersTitle') ?></h3>
    <?= form_open('/admin/users/force-logout-all') ?>
        <button type="submit" class="btn btn-danger btn-sm"
                onclick="return confirm('<?= lang('Admin.forceSignOutAllConfirm') ?>')">
            <i class="bi bi-shield-exclamation"></i> <?= lang('Admin.forceSignOutAll') ?>
        </button>
    <?= form_close() ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= count($users) ?></h2>
                <small class="text-muted"><?= lang('Admin.totalUsers') ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= array_sum(array_column($users, 'weapon_count')) ?></h2>
                <small class="text-muted"><?= lang('Admin.totalWeapons') ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= array_sum(array_column($users, 'session_count')) ?></h2>
                <small class="text-muted"><?= lang('Admin.totalSessions') ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= array_sum(array_column($users, 'location_count')) ?></h2>
                <small class="text-muted"><?= lang('Admin.totalLocations') ?></small>
            </div>
        </div>
    </div>
    <?php $pendingCount = count(array_filter($users, fn($u) => empty($u['is_approved']))); ?>
    <?php if ($pendingCount > 0): ?>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100 border-warning">
            <div class="card-body">
                <h2 class="mb-0 text-warning"><?= $pendingCount ?></h2>
                <small class="text-muted"><?= lang('Admin.pendingUsers') ?></small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="get" action="/admin/users" class="row g-2 align-items-center">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="q"
                           value="<?= esc($search) ?>" placeholder="<?= lang('Admin.searchPlaceholder') ?>">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><?= lang('App.search') ?></button>
                <?php if ($search !== ''): ?>
                    <a href="/admin/users" class="btn btn-outline-secondary"><?= lang('App.clear') ?></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th><?= lang('Admin.username') ?></th>
                    <th><?= lang('Admin.email') ?></th>
                    <th class="text-center"><?= lang('Admin.role') ?></th>
                    <th class="text-center"><?= lang('Admin.twoFa') ?></th>
                    <th class="text-center"><?= lang('App.weapons') ?></th>
                    <th class="text-center"><?= lang('App.sessions') ?></th>
                    <th class="text-center"><?= lang('Settings.locations') ?></th>
                    <th><?= lang('Admin.joined') ?></th>
                    <th class="text-center"><?= lang('App.actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <?= $search !== '' ? lang('Admin.noUsersMatchSearch', [esc($search)]) : lang('Admin.noUsersFound') ?>.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong><?= esc($user['username']) ?></strong></td>
                    <td><?= esc($user['email']) ?></td>
                    <td class="text-center">
                        <?php if (empty($user['is_approved'])): ?>
                            <span class="badge bg-warning text-dark"><?= lang('Admin.pendingBadge') ?></span>
                        <?php elseif (empty($user['is_active'])): ?>
                            <span class="badge bg-danger"><?= lang('Admin.disabledBadge') ?></span>
                        <?php elseif ($user['is_admin']): ?>
                            <span class="badge bg-primary"><?= lang('Admin.adminBadge') ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= lang('Admin.userBadge') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($user['totp_enabled']): ?>
                            <i class="bi bi-shield-check text-success" title="<?= lang('Admin.twoFaEnabled') ?>"></i>
                        <?php else: ?>
                            <i class="bi bi-shield-x text-danger" title="<?= lang('Admin.twoFaNotEnabled') ?>"></i>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $user['weapon_count'] ?></td>
                    <td class="text-center"><?= $user['session_count'] ?></td>
                    <td class="text-center"><?= $user['location_count'] ?></td>
                    <td>
                        <small class="text-muted"><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                    </td>
                    <td class="text-center">
                        <?php if (empty($user['is_approved'])): ?>
                            <?= form_open('/admin/users/approve/' . $user['id'], ['class' => 'd-inline']) ?>
                                <button type="submit" class="btn btn-outline-success btn-sm"
                                        title="<?= lang('Admin.approve') ?>">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            <?= form_close() ?>
                            <?= form_open('/admin/users/reject/' . $user['id'], ['class' => 'd-inline']) ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                        title="<?= lang('Admin.reject') ?>"
                                        onclick="return confirm('<?= lang('Admin.rejectConfirm', [esc($user['username'])]) ?>')">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            <?= form_close() ?>
                        <?php elseif ((int) $user['id'] !== (int) session()->get('user_id')): ?>
                            <?= form_open('/admin/users/toggle-admin/' . $user['id'], ['class' => 'd-inline']) ?>
                                <?php if ($user['is_admin']): ?>
                                    <button type="submit" class="btn btn-outline-warning btn-sm"
                                            title="<?= lang('Admin.demoteFromAdmin') ?>"
                                            onclick="return confirm('<?= lang('Admin.demoteConfirm', [esc($user['username'])]) ?>')">
                                        <i class="bi bi-arrow-down-circle"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-outline-primary btn-sm"
                                            title="<?= lang('Admin.promoteToAdmin') ?>"
                                            onclick="return confirm('<?= lang('Admin.promoteConfirm', [esc($user['username'])]) ?>')">
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </button>
                                <?php endif; ?>
                            <?= form_close() ?>
                            <?= form_open('/admin/users/toggle-active/' . $user['id'], ['class' => 'd-inline']) ?>
                                <?php if ($user['is_active']): ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                            title="<?= lang('Admin.disableUser') ?>"
                                            onclick="return confirm('<?= lang('Admin.disableConfirm', [esc($user['username'])]) ?>')">
                                        <i class="bi bi-person-slash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-outline-success btn-sm"
                                            title="<?= lang('Admin.enableUser') ?>">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                <?php endif; ?>
                            <?= form_close() ?>
                        <?php else: ?>
                            <span class="text-muted" title="<?= lang('Admin.cannotChangeOwnRole') ?>"><i class="bi bi-lock"></i></span>
                        <?php endif; ?>
                        <?php if ((int) $user['id'] !== (int) session()->get('user_id') && ! empty($user['is_approved'])): ?>
                            <?= form_open('/admin/users/force-logout/' . $user['id'], ['class' => 'd-inline']) ?>
                                <button type="submit" class="btn btn-outline-secondary btn-sm"
                                        title="<?= lang('Admin.forceSignOut') ?>"
                                        onclick="return confirm('<?= lang('Admin.forceSignOutConfirm', [esc($user['username'])]) ?>')">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                            <?= form_close() ?>
                        <?php endif; ?>
                        <?php if ($invitesEnabled === '1' && (int) $user['id'] !== (int) session()->get('user_id') && ! empty($user['is_approved'])): ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    title="<?= lang('Admin.setInviteLimit') ?>"
                                    data-bs-toggle="modal" data-bs-target="#inviteLimitModal"
                                    data-user-id="<?= $user['id'] ?>"
                                    data-username="<?= esc($user['username']) ?>"
                                    data-current-limit="<?= isset($inviteLimits[(int)$user['id']]) ? esc($inviteLimits[(int)$user['id']]) : '' ?>">
                                <i class="bi bi-envelope-plus"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($invitesEnabled === '1'): ?>
<!-- Invite limit modal -->
<div class="modal fade" id="inviteLimitModal" tabindex="-1" aria-labelledby="inviteLimitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteLimitModalLabel">
                    <i class="bi bi-envelope-plus"></i> <?= lang('Admin.setInviteLimit') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="inviteLimitForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="mb-3"><strong id="inviteLimitUsername"></strong></p>
                    <label for="inviteLimitInput" class="form-label">
                        <?= lang('Admin.userInviteLimit') ?>
                        <small class="text-muted" id="inviteLimitGlobalNote"></small>
                    </label>
                    <input type="number" name="invite_limit" id="inviteLimitInput"
                           class="form-control" min="0"
                           placeholder="<?= lang('Admin.inviteLimitPlaceholder') ?>">
                    <div class="form-text"><?= lang('Admin.inviteLimitHint') ?></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <?= lang('App.cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <?= lang('Admin.setLimit') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?php if ($invitesEnabled === '1'): ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('inviteLimitModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const userId       = btn.getAttribute('data-user-id');
    const username     = btn.getAttribute('data-username');
    const currentLimit = btn.getAttribute('data-current-limit');
    const globalLimit  = '<?= esc($globalInviteLimit) ?>';

    document.getElementById('inviteLimitUsername').textContent = username;
    document.getElementById('inviteLimitInput').value = currentLimit;
    document.getElementById('inviteLimitGlobalNote').textContent =
        '(<?= lang('Admin.inviteLimitGlobal', ['{g}']) ?>)'.replace('{g}', globalLimit);
    document.getElementById('inviteLimitForm').action = '/admin/invites/set-limit/' + userId;
});
</script>
<?= $this->endSection() ?>
<?php endif; ?>
