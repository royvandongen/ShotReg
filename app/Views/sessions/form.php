<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= ($action === 'create') ? 'New Session' : 'Edit Session' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3><?= ($action === 'create') ? 'Record New Session' : 'Edit Session' ?></h3>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= form_open_multipart(
    ($action === 'create') ? 'sessions/create' : 'sessions/edit/' . $id
) ?>

    <div class="mb-3">
        <label for="session_date" class="form-label">Date</label>
        <input type="date" class="form-control" id="session_date" name="session_date"
               value="<?= esc($session['session_date'] ?? date('Y-m-d')) ?>" required>
    </div>

    <div class="mb-3">
        <label for="location_id" class="form-label">Location</label>
        <div class="input-group">
            <select class="form-select" id="location_id" name="location_id">
                <option value="">-- No location --</option>
                <?php if (! empty($locations)): ?>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['id'] ?>"
                            <?= (($session['location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>>
                            <?= esc($loc['name']) ?>
                            <?php if (! empty($loc['address'])): ?>
                                (<?= esc($loc['address']) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#addLocationModal">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>

    <div class="mb-3">
        <label for="weapon_id" class="form-label">Weapon</label>
        <div class="input-group">
            <select class="form-select" id="weapon_id" name="weapon_id" required>
                <option value="">Select a weapon...</option>
                <?php foreach ($weapons as $w): ?>
                    <option value="<?= $w['id'] ?>"
                        <?= (($session['weapon_id'] ?? '') == $w['id']) ? 'selected' : '' ?>>
                        <?= esc($w['name']) ?> (<?= esc($w['type']) ?> - <?= esc($w['caliber']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#addWeaponModal">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Distance / Lane</label>
        <?php if (! empty($laneTypes)): ?>
        <div class="btn-group w-100 mb-2" role="group">
            <?php foreach ($laneTypes as $i => $lt): ?>
                <input type="radio" class="btn-check" name="distance_preset"
                       id="d_<?= $i ?>" value="<?= esc($lt['value']) ?>">
                <label class="btn btn-outline-primary" for="d_<?= $i ?>"><?= esc($lt['label']) ?></label>
            <?php endforeach; ?>
            <input type="radio" class="btn-check" name="distance_preset" id="dcustom" value="custom">
            <label class="btn btn-outline-primary" for="dcustom">Custom</label>
        </div>
        <?php endif; ?>
        <input type="text" class="form-control" id="distance" name="distance"
               value="<?= esc($session['distance'] ?? '') ?>"
               placeholder="e.g. 25m" required>
    </div>

    <div class="mb-3">
        <label for="photos" class="form-label">Shooting Card Photos</label>
        <input type="file" class="form-control" id="photos" name="photos[]"
               multiple accept="image/*" capture="environment">
        <div class="form-text">Upload photos of your shooting cards. You can select multiple.</div>
    </div>

    <?php if (! empty($photos)): ?>
    <div class="mb-3">
        <label class="form-label">Existing Photos <small class="text-muted">(drag to reorder)</small></label>
        <div class="row g-2" id="photoSortable">
            <?php foreach ($photos as $photo): ?>
                <div class="col-4 col-md-3 text-center sortable-item" data-photo-id="<?= $photo['id'] ?>">
                    <div class="position-relative">
                        <img src="/photos/thumb/<?= esc($photo['thumbnail']) ?>"
                             class="card-thumbnail mb-1 sortable-handle" alt="<?= esc($photo['original_name']) ?>"
                             style="cursor: grab;">
                        <span class="badge bg-dark position-absolute top-0 start-0 photo-order"><?= ((int)$photo['sort_order']) + 1 ?></span>
                    </div>
                    <form method="post" action="/sessions/delete-photo/<?= $photo['id'] ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Remove this photo?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes"
                  rows="3"><?= esc($session['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg"></i> Save Session
    </button>
    <a href="/sessions" class="btn btn-secondary">Cancel</a>

<?= form_close() ?>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="modal_location_name"
                           placeholder="e.g. Shooting Range De Bilt">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" id="modal_location_address"
                           placeholder="e.g. Sportlaan 12, 3730 AB De Bilt">
                </div>
                <div id="modal_location_errors" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLocationBtn">Save Location</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Weapon Modal -->
<div class="modal fade" id="addWeaponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Weapon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name/Model</label>
                    <input type="text" class="form-control" id="modal_weapon_name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" id="modal_weapon_type">
                        <option value="pistol">Pistol</option>
                        <option value="rifle">Rifle</option>
                        <option value="shotgun">Shotgun</option>
                        <option value="revolver">Revolver</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Caliber</label>
                    <input type="text" class="form-control" id="modal_weapon_caliber"
                           placeholder="e.g. 9mm, .22 LR">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sighting</label>
                    <select class="form-select" id="modal_weapon_sighting">
                        <option value="">-- None --</option>
                        <?php if (! empty($sightings)): ?>
                            <?php foreach ($sightings as $s): ?>
                                <option value="<?= esc($s['value']) ?>"><?= esc($s['label']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ownership</label>
                    <select class="form-select" id="modal_weapon_ownership">
                        <option value="personal">Personal</option>
                        <option value="association">Association</option>
                    </select>
                </div>
                <div id="modal_weapon_errors" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWeaponBtn">Save Weapon</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
// Drag-and-drop photo reordering
(function() {
    var container = document.getElementById('photoSortable');
    if (!container) return;

    Sortable.create(container, {
        animation: 150,
        handle: '.sortable-handle',
        draggable: '.sortable-item',
        onEnd: function() {
            var items = container.querySelectorAll('.sortable-item');
            var photoIds = [];
            items.forEach(function(item, index) {
                photoIds.push(item.dataset.photoId);
                var badge = item.querySelector('.photo-order');
                if (badge) badge.textContent = index + 1;
            });

            var data = new FormData();
            photoIds.forEach(function(id) {
                data.append('photo_ids[]', id);
            });
            data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('/sessions/reorder-photos', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        }
    });
})();

// Distance preset buttons
document.querySelectorAll('[name="distance_preset"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var distanceInput = document.getElementById('distance');
        if (this.value === 'custom') {
            distanceInput.value = '';
            distanceInput.focus();
        } else {
            distanceInput.value = this.value;
        }
    });
});

// Pre-select the right preset on page load
(function() {
    var current = document.getElementById('distance').value;
    var presets = {};
    <?php if (! empty($laneTypes)): ?>
        <?php foreach ($laneTypes as $i => $lt): ?>
            presets['<?= esc($lt['value'], 'js') ?>'] = 'd_<?= $i ?>';
        <?php endforeach; ?>
    <?php endif; ?>
    if (presets[current]) {
        document.getElementById(presets[current]).checked = true;
    } else if (current) {
        var customBtn = document.getElementById('dcustom');
        if (customBtn) customBtn.checked = true;
    }
})();

// Inline location creation via AJAX
document.getElementById('saveLocationBtn').addEventListener('click', function() {
    var data = new FormData();
    data.append('name', document.getElementById('modal_location_name').value);
    data.append('address', document.getElementById('modal_location_address').value);
    data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('/sessions/ajax-create-location', {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var select = document.getElementById('location_id');
            var option = new Option(result.location.name, result.location.id, true, true);
            select.add(option);
            bootstrap.Modal.getInstance(document.getElementById('addLocationModal')).hide();
            document.getElementById('modal_location_name').value = '';
            document.getElementById('modal_location_address').value = '';
            document.getElementById('modal_location_errors').classList.add('d-none');
        } else {
            var errDiv = document.getElementById('modal_location_errors');
            errDiv.textContent = Object.values(result.errors).join(', ');
            errDiv.classList.remove('d-none');
        }
    });
});

// Inline weapon creation via AJAX
document.getElementById('saveWeaponBtn').addEventListener('click', function() {
    var data = new FormData();
    data.append('name', document.getElementById('modal_weapon_name').value);
    data.append('type', document.getElementById('modal_weapon_type').value);
    data.append('caliber', document.getElementById('modal_weapon_caliber').value);
    data.append('sighting', document.getElementById('modal_weapon_sighting').value);
    data.append('ownership', document.getElementById('modal_weapon_ownership').value);
    data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('/weapons/ajax-create', {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            var select = document.getElementById('weapon_id');
            var option = new Option(result.weapon.name, result.weapon.id, true, true);
            select.add(option);
            bootstrap.Modal.getInstance(document.getElementById('addWeaponModal')).hide();
            // Clear modal fields
            document.getElementById('modal_weapon_name').value = '';
            document.getElementById('modal_weapon_caliber').value = '';
            document.getElementById('modal_weapon_sighting').value = '';
            document.getElementById('modal_weapon_errors').classList.add('d-none');
        } else {
            var errDiv = document.getElementById('modal_weapon_errors');
            errDiv.textContent = Object.values(result.errors).join(', ');
            errDiv.classList.remove('d-none');
        }
    });
});
</script>
<?= $this->endSection() ?>
