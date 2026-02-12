<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Sessions.sessionDate') ?> <?= esc($session['session_date']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?= lang('Sessions.sessionDate') ?>: <?= esc($session['session_date']) ?></h3>
    <div>
        <a href="/sessions/edit/<?= $session['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> <?= lang('App.edit') ?>
        </a>
        <a href="/sessions" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <?= lang('App.back') ?>
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6 col-md-3 mb-2">
                <strong><?= lang('App.date') ?></strong><br>
                <?= esc($session['session_date']) ?>
            </div>
            <?php if (! empty($session['location_name'])): ?>
            <div class="col-sm-6 col-md-3 mb-2">
                <strong><?= lang('Sessions.location') ?></strong><br>
                <?= esc($session['location_name']) ?>
                <?php if (! empty($session['location_address'])): ?>
                    <br><small class="text-muted"><?= esc($session['location_address']) ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="col-sm-6 col-md-3 mb-2">
                <strong><?= lang('Sessions.weapon') ?></strong><br>
                <?= esc($session['weapon_name']) ?>
                <small class="text-muted">(<?= esc($session['weapon_type']) ?>)</small>
            </div>
            <div class="col-sm-6 col-md-3 mb-2">
                <strong><?= lang('Sessions.caliber') ?></strong><br>
                <?= esc($session['caliber']) ?>
            </div>
            <div class="col-sm-6 col-md-3 mb-2">
                <strong><?= lang('Sessions.distance') ?></strong><br>
                <?= esc($session['distance']) ?>
            </div>
        </div>
        <?php if (! empty($session['notes'])): ?>
            <hr>
            <strong><?= lang('App.notes') ?></strong><br>
            <?= nl2br(esc($session['notes'])) ?>
        <?php endif; ?>
    </div>
</div>

<?php if (! empty($photos)): ?>
<h5><?= lang('Sessions.shootingCards') ?> (<?= count($photos) ?>)</h5>
<div class="row g-3 photo-grid">
    <?php foreach ($photos as $index => $photo): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="position-relative">
                <a href="/photos/<?= esc($photo['filename']) ?>" target="_blank">
                    <img src="/photos/thumb/<?= esc($photo['thumbnail']) ?>"
                         class="img-fluid rounded shadow-sm"
                         alt="<?= esc($photo['original_name']) ?>">
                </a>
                <span class="badge bg-dark position-absolute top-0 start-0 m-1">#<?= $index + 1 ?></span>
            </div>
            <small class="text-muted d-block mt-1"><?= esc($photo['original_name']) ?></small>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-light">
    <?= lang('Sessions.noPhotos') ?>
    <a href="/sessions/edit/<?= $session['id'] ?>"><?= lang('Sessions.addPhotos') ?></a>.
</div>
<?php endif; ?>
<?= $this->endSection() ?>
