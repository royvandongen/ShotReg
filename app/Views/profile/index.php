<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4">Profile</h3>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Account Details</strong></div>
            <div class="card-body">
                <?= form_open('/profile/update') ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= esc($user['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= esc($user['email']) ?>" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   value="<?= esc($user['first_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   value="<?= esc($user['last_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="knsa_member_id" class="form-label">KNSA Member ID (Lidnummer)</label>
                        <input type="text" class="form-control" id="knsa_member_id" name="knsa_member_id"
                               value="<?= esc($user['knsa_member_id'] ?? '') ?>"
                               placeholder="e.g. 123456">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Change Password</strong></div>
            <div class="card-body">
                <?= form_open('/profile/change-password') ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password"
                               name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password"
                               name="new_password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
