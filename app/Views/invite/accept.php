<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?><?= lang('Invite.acceptTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="card-title mb-1"><?= lang('Invite.acceptTitle') ?></h4>
        <p class="text-muted small mb-3">
            <?= lang('Invite.invitedAs') ?> <strong><?= esc($email) ?></strong>
        </p>

        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= form_open('invite/' . esc($token)) ?>
            <div class="mb-3">
                <label for="username" class="form-label"><?= lang('Invite.chooseUsername') ?></label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= set_value('username') ?>" required autofocus
                       minlength="3" maxlength="50">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><?= lang('Invite.choosePassword') ?></label>
                <input type="password" class="form-control" id="password" name="password"
                       required minlength="8">
                <div class="form-text"><?= lang('Auth.passwordMinLength') ?></div>
            </div>
            <div class="mb-3">
                <label for="password_confirm" class="form-label"><?= lang('Auth.confirmPassword') ?></label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= lang('Invite.acceptInvite') ?></button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
