<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Settings.title') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3><i class="bi bi-gear"></i> <?= lang('Settings.title') ?></h3>

<!-- Locations -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= lang('Settings.locations') ?></h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addLocationModal">
            <i class="bi bi-plus-lg"></i> <?= lang('App.add') ?>
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($locations)): ?>
            <p class="text-muted mb-0"><?= lang('Settings.noLocations') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th><?= lang('App.name') ?></th>
                            <th><?= lang('App.address') ?></th>
                            <th><?= lang('App.default') ?></th>
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
                                    <span class="badge bg-success"><?= lang('App.default') ?></span>
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
                                      class="d-inline" onsubmit="return confirm('<?= lang('Settings.removeLocationConfirm') ?>')">
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
        <h5 class="mb-0"><?= lang('Settings.laneTypes') ?></h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addLaneTypeModal">
            <i class="bi bi-plus-lg"></i> <?= lang('App.add') ?>
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($laneTypes)): ?>
            <p class="text-muted mb-0"><?= lang('Settings.noLaneTypes') ?></p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($laneTypes as $lt): ?>
                    <span class="badge bg-primary d-flex align-items-center gap-1 fs-6 fw-normal">
                        <?= esc($lt['label']) ?>
                        <form method="post" action="/settings/delete-option/<?= $lt['id'] ?>"
                              class="d-inline" onsubmit="return confirm('<?= lang('Settings.removeLaneConfirm') ?>')">
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
        <h5 class="mb-0"><?= lang('Settings.sightingOptions') ?></h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addSightingModal">
            <i class="bi bi-plus-lg"></i> <?= lang('App.add') ?>
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($sightings)): ?>
            <p class="text-muted mb-0"><?= lang('Settings.noSightings') ?></p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($sightings as $s): ?>
                    <span class="badge bg-secondary d-flex align-items-center gap-1 fs-6 fw-normal">
                        <?= esc($s['label']) ?>
                        <form method="post" action="/settings/delete-option/<?= $s['id'] ?>"
                              class="d-inline" onsubmit="return confirm('<?= lang('Settings.removeSightingConfirm') ?>')">
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
        <h5 class="mb-0"><?= lang('Settings.defaultSettings') ?></h5>
    </div>
    <div class="card-body">
        <?= form_open('settings/save-defaults') ?>
            <div class="row align-items-end">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="default_ownership" class="form-label"><?= lang('Settings.defaultOwnership') ?></label>
                    <select class="form-select" id="default_ownership" name="default_ownership">
                        <option value="personal" <?= ($defaultOwnership === 'personal') ? 'selected' : '' ?>>
                            <?= lang('App.personal') ?>
                        </option>
                        <option value="association" <?= ($defaultOwnership === 'association') ? 'selected' : '' ?>>
                            <?= lang('App.association') ?>
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> <?= lang('Settings.saveDefaults') ?>
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
                <h5 class="modal-title"><?= lang('Settings.addLocation') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><?= lang('Settings.locationName') ?></label>
                    <input type="text" class="form-control" name="name" required
                           placeholder="<?= lang('Settings.locationNamePlaceholder') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= lang('Settings.locationAddress') ?></label>
                    <input type="text" class="form-control" name="address"
                           placeholder="<?= lang('Settings.locationAddressPlaceholder') ?>">
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="add_loc_default" name="is_default" value="1">
                    <label class="form-check-label" for="add_loc_default"><?= lang('Settings.setAsDefault') ?></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('App.cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= lang('Settings.addLocation') ?></button>
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
                    <h5 class="modal-title"><?= lang('Settings.editLocation') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= lang('Settings.locationName') ?></label>
                        <input type="text" class="form-control" name="name" id="edit_loc_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= lang('Settings.locationAddress') ?></label>
                        <input type="text" class="form-control" name="address" id="edit_loc_address">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_loc_default" name="is_default" value="1">
                        <label class="form-check-label" for="edit_loc_default"><?= lang('Settings.setAsDefault') ?></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('App.cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('App.saveChanges') ?></button>
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
                <h5 class="modal-title"><?= lang('Settings.addLaneType') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><?= lang('Settings.laneLabel') ?></label>
                    <input type="text" class="form-control" name="label" required
                           placeholder="<?= lang('Settings.laneLabelPlaceholder') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= lang('Settings.laneValue') ?></label>
                    <input type="text" class="form-control" name="value" required
                           placeholder="<?= lang('Settings.laneValuePlaceholder') ?>">
                    <div class="form-text"><?= lang('Settings.laneValueHelp') ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('App.cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= lang('App.add') ?></button>
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
                <h5 class="modal-title"><?= lang('Settings.addSighting') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><?= lang('Settings.sightingLabel') ?></label>
                    <input type="text" class="form-control" name="label" required
                           placeholder="<?= lang('Settings.sightingLabelPlaceholder') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= lang('Settings.sightingValue') ?></label>
                    <input type="text" class="form-control" name="value" required
                           placeholder="<?= lang('Settings.sightingValuePlaceholder') ?>">
                    <div class="form-text"><?= lang('Settings.sightingValueHelp') ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('App.cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= lang('App.add') ?></button>
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
