<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= ($action === 'create') ? lang('Weapons.createTitle') : lang('Weapons.editTitle') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3><?= ($action === 'create') ? lang('Weapons.createTitle') : lang('Weapons.editTitle') ?></h3>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= form_open(($action === 'create') ? 'weapons/create' : 'weapons/edit/' . $id, ['enctype' => 'multipart/form-data']) ?>

    <div class="mb-3">
        <label for="name" class="form-label"><?= lang('Weapons.nameModel') ?></label>
        <input type="text" class="form-control" id="name" name="name"
               value="<?= esc($weapon['name'] ?? '') ?>" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="type" class="form-label"><?= lang('App.type') ?></label>
            <select class="form-select" id="type" name="type" required>
                <?php foreach (['pistol', 'rifle', 'shotgun', 'revolver', 'other'] as $t): ?>
                    <option value="<?= $t ?>" <?= (($weapon['type'] ?? '') === $t) ? 'selected' : '' ?>>
                        <?= lang('App.' . $t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="caliber" class="form-label"><?= lang('Weapons.caliberAmmo') ?></label>
            <input type="text" class="form-control" id="caliber" name="caliber"
                   value="<?= esc($weapon['caliber'] ?? '') ?>" required
                   placeholder="<?= lang('Weapons.caliberPlaceholder') ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="sighting" class="form-label"><?= lang('Weapons.sighting') ?></label>
            <select class="form-select" id="sighting" name="sighting">
                <option value=""><?= lang('App.none') ?></option>
                <?php if (! empty($sightings)): ?>
                    <?php foreach ($sightings as $s): ?>
                        <option value="<?= esc($s['value']) ?>" <?= (($weapon['sighting'] ?? '') === $s['value']) ? 'selected' : '' ?>>
                            <?= esc($s['label']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="ownership" class="form-label"><?= lang('Weapons.ownership') ?></label>
            <select class="form-select" id="ownership" name="ownership" required>
                <option value="personal" <?= (($weapon['ownership'] ?? '') === 'personal') ? 'selected' : '' ?>>
                    <?= lang('App.personal') ?>
                </option>
                <option value="association" <?= (($weapon['ownership'] ?? '') === 'association') ? 'selected' : '' ?>>
                    <?= lang('App.association') ?>
                </option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="notes" class="form-label"><?= lang('App.notes') ?></label>
        <textarea class="form-control" id="notes" name="notes"
                  rows="2"><?= esc($weapon['notes'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label"><?= lang('Weapons.photo') ?></label>
        <?php if (! empty($weapon['photo'])): ?>
            <div class="mb-2 d-flex align-items-center gap-3">
                <img src="/weapons/photo/thumb/<?= esc($weapon['photo']) ?>"
                     alt="<?= lang('Weapons.currentPhoto') ?>"
                     style="width:80px;height:80px;object-fit:cover;border-radius:6px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                    <label class="form-check-label text-danger" for="remove_photo">
                        <?= lang('Weapons.removePhoto') ?>
                    </label>
                </div>
            </div>
        <?php endif; ?>
        <input type="file" class="form-control" name="weapon_photo" id="weapon_photo"
               accept="image/jpeg,image/png,image/webp">
        <div class="form-text"><?= lang('Weapons.photoHint') ?></div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg"></i> <?= lang('Weapons.saveWeapon') ?>
    </button>
    <a href="/weapons" class="btn btn-secondary"><?= lang('App.cancel') ?></a>

<?= form_close() ?>
<?= $this->endSection() ?>
