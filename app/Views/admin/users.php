<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Users<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4">Users</h3>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= count($users) ?></h2>
                <small class="text-muted">Total Users</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= array_sum(array_column($users, 'weapon_count')) ?></h2>
                <small class="text-muted">Total Weapons</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= array_sum(array_column($users, 'session_count')) ?></h2>
                <small class="text-muted">Total Sessions</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= array_sum(array_column($users, 'location_count')) ?></h2>
                <small class="text-muted">Total Locations</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="get" action="/admin/users" class="row g-2 align-items-center">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="q"
                           value="<?= esc($search) ?>" placeholder="Search by username or email...">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="/admin/users" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">2FA</th>
                    <th class="text-center">Weapons</th>
                    <th class="text-center">Sessions</th>
                    <th class="text-center">Locations</th>
                    <th>Joined</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        No users found<?= $search !== '' ? ' matching "' . esc($search) . '"' : '' ?>.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong><?= esc($user['username']) ?></strong></td>
                    <td><?= esc($user['email']) ?></td>
                    <td class="text-center">
                        <?php if ($user['is_admin']): ?>
                            <span class="badge bg-primary">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">User</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($user['totp_enabled']): ?>
                            <i class="bi bi-shield-check text-success" title="2FA enabled"></i>
                        <?php else: ?>
                            <i class="bi bi-shield-x text-danger" title="2FA not enabled"></i>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $user['weapon_count'] ?></td>
                    <td class="text-center"><?= $user['session_count'] ?></td>
                    <td class="text-center"><?= $user['location_count'] ?></td>
                    <td>
                        <small class="text-muted"><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                    </td>
                    <td class="text-center">
                        <?php if ((int) $user['id'] !== (int) session()->get('user_id')): ?>
                            <?= form_open('/admin/users/toggle-admin/' . $user['id'], ['class' => 'd-inline']) ?>
                                <?php if ($user['is_admin']): ?>
                                    <button type="submit" class="btn btn-outline-warning btn-sm"
                                            title="Demote from admin"
                                            onclick="return confirm('Demote <?= esc($user['username']) ?> from admin?')">
                                        <i class="bi bi-arrow-down-circle"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-outline-primary btn-sm"
                                            title="Promote to admin"
                                            onclick="return confirm('Promote <?= esc($user['username']) ?> to admin?')">
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </button>
                                <?php endif; ?>
                            <?= form_close() ?>
                        <?php else: ?>
                            <span class="text-muted" title="You cannot change your own role"><i class="bi bi-lock"></i></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
