<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= ($action === 'create') ? 'Add Weapon' : 'Edit Weapon' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3><?= ($action === 'create') ? 'Add Weapon' : 'Edit Weapon' ?></h3>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= form_open(($action === 'create') ? 'weapons/create' : 'weapons/edit/' . $id) ?>

    <div class="mb-3">
        <label for="name" class="form-label">Name / Model</label>
        <input type="text" class="form-control" id="name" name="name"
               value="<?= esc($weapon['name'] ?? '') ?>" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="type" class="form-label">Type</label>
            <select class="form-select" id="type" name="type" required>
                <?php foreach (['pistol', 'rifle', 'shotgun', 'revolver', 'other'] as $t): ?>
                    <option value="<?= $t ?>" <?= (($weapon['type'] ?? '') === $t) ? 'selected' : '' ?>>
                        <?= ucfirst($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="caliber" class="form-label">Caliber / Ammo</label>
            <input type="text" class="form-control" id="caliber" name="caliber"
                   value="<?= esc($weapon['caliber'] ?? '') ?>" required
                   placeholder="e.g. 9mm, .22 LR, .308 Win">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="sighting" class="form-label">Sighting</label>
            <select class="form-select" id="sighting" name="sighting">
                <option value="">-- None --</option>
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
            <label for="ownership" class="form-label">Ownership</label>
            <select class="form-select" id="ownership" name="ownership" required>
                <option value="personal" <?= (($weapon['ownership'] ?? '') === 'personal') ? 'selected' : '' ?>>
                    Personal
                </option>
                <option value="association" <?= (($weapon['ownership'] ?? '') === 'association') ? 'selected' : '' ?>>
                    Association
                </option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes"
                  rows="2"><?= esc($weapon['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg"></i> Save Weapon
    </button>
    <a href="/weapons" class="btn btn-secondary">Cancel</a>

<?= form_close() ?>
<?= $this->endSection() ?>
