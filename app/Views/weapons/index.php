<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Weapons<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Weapons Catalog</h3>
    <a href="/weapons/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Weapon
    </a>
</div>

<?php if (empty($weapons)): ?>
    <div class="alert alert-info">
        No weapons in your catalog yet. <a href="/weapons/create">Add your first weapon</a>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Caliber</th>
                    <th>Sighting</th>
                    <th>Ownership</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weapons as $weapon): ?>
                <tr>
                    <td><?= esc($weapon['name']) ?></td>
                    <td><span class="badge bg-secondary"><?= esc(ucfirst($weapon['type'])) ?></span></td>
                    <td><?= esc($weapon['caliber']) ?></td>
                    <td><?= esc(ucfirst($weapon['sighting'] ?? '-')) ?></td>
                    <td>
                        <?php if ($weapon['ownership'] === 'association'): ?>
                            <span class="badge bg-info">Association</span>
                        <?php else: ?>
                            <span class="badge bg-success">Personal</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="/weapons/edit/<?= $weapon['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="/weapons/delete/<?= $weapon['id'] ?>"
                              class="d-inline" onsubmit="return confirm('Delete this weapon?')">
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

    <!-- Mobile card view -->
    <div class="d-lg-none">
        <?php foreach ($weapons as $weapon): ?>
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= esc($weapon['name']) ?></strong><br>
                        <small class="text-muted">
                            <?= esc(ucfirst($weapon['type'])) ?> &middot; <?= esc($weapon['caliber']) ?>
                            <?php if ($weapon['ownership'] === 'association'): ?>
                                &middot; <span class="badge bg-info">Assoc</span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div>
                        <a href="/weapons/edit/<?= $weapon['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    @media (max-width: 991.98px) {
        .table-responsive { display: none; }
    }
    @media (min-width: 992px) {
        .d-lg-none { display: none !important; }
    }
</style>
<?= $this->endSection() ?>
