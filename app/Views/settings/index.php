<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Settings<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3><i class="bi bi-gear"></i> Settings</h3>

<!-- Locations -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Locations</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addLocationModal">
            <i class="bi bi-plus-lg"></i> Add
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($locations)): ?>
            <p class="text-muted mb-0">No locations configured yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Default</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locations as $loc): ?>
                        <tr>
                            <td><?= esc($loc['name']) ?></td>
                            <td><?= esc($loc['address'] ?? '') ?></td>
                            <td>
                                <?php if ($loc['is_default']): ?>
                                    <span class="badge bg-success">Default</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-location-btn"
                                        data-id="<?= $loc['id'] ?>"
                                        data-name="<?= esc($loc['name']) ?>"
                                        data-address="<?= esc($loc['address'] ?? '') ?>"
                                        data-default="<?= $loc['is_default'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="post" action="/settings/delete-location/<?= $loc['id'] ?>"
                                      class="d-inline" onsubmit="return confirm('Remove this location?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lane Types -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lane Types / Distances</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addLaneTypeModal">
            <i class="bi bi-plus-lg"></i> Add
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($laneTypes)): ?>
            <p class="text-muted mb-0">No lane types configured.</p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($laneTypes as $lt): ?>
                    <span class="badge bg-primary d-flex align-items-center gap-1 fs-6 fw-normal">
                        <?= esc($lt['label']) ?>
                        <form method="post" action="/settings/delete-option/<?= $lt['id'] ?>"
                              class="d-inline" onsubmit="return confirm('Remove this lane type?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.6rem;"></button>
                        </form>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sighting Options -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sighting Options</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addSightingModal">
            <i class="bi bi-plus-lg"></i> Add
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($sightings)): ?>
            <p class="text-muted mb-0">No sighting options configured.</p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($sightings as $s): ?>
                    <span class="badge bg-secondary d-flex align-items-center gap-1 fs-6 fw-normal">
                        <?= esc($s['label']) ?>
                        <form method="post" action="/settings/delete-option/<?= $s['id'] ?>"
                              class="d-inline" onsubmit="return confirm('Remove this sighting option?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.6rem;"></button>
                        </form>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Default Settings -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Default Settings</h5>
    </div>
    <div class="card-body">
        <?= form_open('settings/save-defaults') ?>
            <div class="row align-items-end">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="default_ownership" class="form-label">Default Ownership</label>
                    <select class="form-select" id="default_ownership" name="default_ownership">
                        <option value="personal" <?= ($defaultOwnership === 'personal') ? 'selected' : '' ?>>
                            Personal
                        </option>
                        <option value="association" <?= ($defaultOwnership === 'association') ? 'selected' : '' ?>>
                            Association
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Defaults
                    </button>
                </div>
            </div>
        <?= form_close() ?>
    </div>
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open('settings/add-location') ?>
            <div class="modal-header">
                <h5 class="modal-title">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="name" required
                           placeholder="e.g. Shooting Range De Bilt">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address"
                           placeholder="e.g. Sportlaan 12, 3730 AB De Bilt">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="add_loc_default" name="is_default" value="1">
                    <label class="form-check-label" for="add_loc_default">Set as default location</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Location</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="editLocationForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_loc_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" id="edit_loc_address">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_loc_default" name="is_default" value="1">
                        <label class="form-check-label" for="edit_loc_default">Set as default location</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Lane Type Modal -->
<div class="modal fade" id="addLaneTypeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <?= form_open('settings/add-option') ?>
            <input type="hidden" name="type" value="lane_type">
            <div class="modal-header">
                <h5 class="modal-title">Add Lane Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" class="form-control" name="label" required
                           placeholder="e.g. 200m">
                </div>
                <div class="mb-3">
                    <label class="form-label">Value</label>
                    <input type="text" class="form-control" name="value" required
                           placeholder="e.g. 200m">
                    <div class="form-text">Stored value (usually same as label)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Add Sighting Modal -->
<div class="modal fade" id="addSightingModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <?= form_open('settings/add-option') ?>
            <input type="hidden" name="type" value="sighting">
            <div class="modal-header">
                <h5 class="modal-title">Add Sighting Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Label</label>
                    <input type="text" class="form-control" name="label" required
                           placeholder="e.g. Red Dot">
                </div>
                <div class="mb-3">
                    <label class="form-label">Value</label>
                    <input type="text" class="form-control" name="value" required
                           placeholder="e.g. red_dot">
                    <div class="form-text">Internal value (lowercase, no spaces)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Edit location modal
document.querySelectorAll('.edit-location-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        document.getElementById('editLocationForm').action = '/settings/edit-location/' + id;
        document.getElementById('edit_loc_name').value = this.dataset.name;
        document.getElementById('edit_loc_address').value = this.dataset.address;
        document.getElementById('edit_loc_default').checked = this.dataset.default === '1';
        new bootstrap.Modal(document.getElementById('editLocationModal')).show();
    });
});
</script>
<?= $this->endSection() ?>
