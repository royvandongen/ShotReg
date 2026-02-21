<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Admin.emailSettingsTitle') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4"><?= lang('Admin.emailSettingsTitle') ?></h3>

<div class="card mb-4">
    <div class="card-header"><strong><?= lang('Admin.emailProtocol') ?> / SMTP</strong></div>
    <div class="card-body">
        <div class="alert alert-info alert-dismissible fade show small" role="alert">
            <?= lang('Admin.m365Note') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <?= form_open('/admin/email') ?>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label for="email_protocol" class="form-label"><?= lang('Admin.emailProtocol') ?></label>
                    <select class="form-select" id="email_protocol" name="email_protocol">
                        <option value="smtp" <?= $emailProtocol === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                        <option value="mail" <?= $emailProtocol === 'mail' ? 'selected' : '' ?>>PHP mail()</option>
                        <option value="sendmail" <?= $emailProtocol === 'sendmail' ? 'selected' : '' ?>>Sendmail</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="smtp_host" class="form-label"><?= lang('Admin.smtpHost') ?></label>
                    <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                           value="<?= esc($smtpHost) ?>" placeholder="smtp-mail.outlook.com">
                </div>
                <div class="col-md-2">
                    <label for="smtp_port" class="form-label"><?= lang('Admin.smtpPort') ?></label>
                    <input type="number" class="form-control" id="smtp_port" name="smtp_port"
                           value="<?= esc($smtpPort) ?>" placeholder="587">
                </div>
                <div class="col-md-2">
                    <label for="smtp_crypto" class="form-label"><?= lang('Admin.smtpCrypto') ?></label>
                    <select class="form-select" id="smtp_crypto" name="smtp_crypto">
                        <option value="tls" <?= $smtpCrypto === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= $smtpCrypto === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="" <?= $smtpCrypto === '' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="smtp_user" class="form-label"><?= lang('Admin.smtpUser') ?></label>
                    <input type="text" class="form-control" id="smtp_user" name="smtp_user"
                           value="<?= esc($smtpUser) ?>" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="smtp_pass" class="form-label"><?= lang('Admin.smtpPass') ?></label>
                    <input type="password" class="form-control" id="smtp_pass" name="smtp_pass"
                           autocomplete="new-password" placeholder="<?= lang('Admin.leaveBlankToKeep') ?>">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="email_from_address" class="form-label"><?= lang('Admin.emailFromAddress') ?></label>
                    <input type="email" class="form-control" id="email_from_address" name="email_from_address"
                           value="<?= esc($emailFromAddress) ?>">
                </div>
                <div class="col-md-6">
                    <label for="email_from_name" class="form-label"><?= lang('Admin.emailFromName') ?></label>
                    <input type="text" class="form-control" id="email_from_name" name="email_from_name"
                           value="<?= esc($emailFromName) ?>">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= lang('App.save') ?></button>
            </div>
        <?= form_close() ?>

        <?= form_open('/admin/email/test') ?>
            <div class="mt-3 pt-3 border-top">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope"></i> <?= lang('Admin.sendTestEmail') ?>
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><strong><?= lang('Admin.templateInviteTitle') ?></strong></div>
    <div class="card-body">
        <p class="text-muted small">
            <?= lang('Admin.templatePlaceholders') ?>
            <code>{site_name}</code>, <code>{inviter_name}</code>, <code>{invite_link}</code>, <code>{expires_hours}</code>
        </p>
        <?= form_open('/admin/email/save-template/invite') ?>
            <textarea name="template" id="tpl-invite" class="form-control font-monospace mb-3"
                      rows="16" style="resize: vertical;"><?= esc($templateInvite) ?></textarea>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= lang('Admin.saveTemplate') ?></button>
                <button type="button" class="btn btn-outline-secondary"
                        onclick="previewTemplate('tpl-invite', {
                            '{site_name}':    'Shotr',
                            '{inviter_name}': 'Admin',
                            '{invite_link}':  '#',
                            '{expires_hours}': '48'
                        })">
                    <i class="bi bi-eye"></i> <?= lang('Admin.previewTemplate') ?>
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong><?= lang('Admin.templateResetTitle') ?></strong></div>
    <div class="card-body">
        <p class="text-muted small">
            <?= lang('Admin.templatePlaceholders') ?>
            <code>{site_name}</code>, <code>{password_reset_link}</code>, <code>{expires_minutes}</code>
        </p>
        <?= form_open('/admin/email/save-template/reset') ?>
            <textarea name="template" id="tpl-reset" class="form-control font-monospace mb-3"
                      rows="16" style="resize: vertical;"><?= esc($templateReset) ?></textarea>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= lang('Admin.saveTemplate') ?></button>
                <button type="button" class="btn btn-outline-secondary"
                        onclick="previewTemplate('tpl-reset', {
                            '{site_name}':          'Shotr',
                            '{reset_link}':         '#',
                            '{expires_minutes}':    '60'
                        })">
                    <i class="bi bi-eye"></i> <?= lang('Admin.previewTemplate') ?>
                </button>
            </div>
        <?= form_close() ?>
    </div>
</div>

<!-- Shared template preview modal -->
<div class="modal fade" id="tplPreviewModal" tabindex="-1" aria-labelledby="tplPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tplPreviewModalLabel">
                    <i class="bi bi-eye"></i> <?= lang('Admin.previewTemplate') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="tplPreviewFrame"
                        style="width:100%;height:620px;border:none;"
                        sandbox="allow-same-origin"></iframe>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function previewTemplate(textareaId, placeholders) {
    let html = document.getElementById(textareaId).value;
    for (const [key, value] of Object.entries(placeholders)) {
        html = html.split(key).join(value);
    }
    const frame = document.getElementById('tplPreviewFrame');
    frame.srcdoc = html;
    new bootstrap.Modal(document.getElementById('tplPreviewModal')).show();
}
</script>
<?= $this->endSection() ?>
