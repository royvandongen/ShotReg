<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('Dashboard.title') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<h3 class="mb-4"><?= lang('Dashboard.title') ?></h3>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= $sessionCount ?></h2>
                <small class="text-muted"><?= lang('Dashboard.sessions') ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <h2 class="mb-0"><?= $weaponCount ?></h2>
                <small class="text-muted"><?= lang('Dashboard.weapons') ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <a href="/sessions/create" class="card text-center bg-primary text-white text-decoration-none h-100">
            <div class="card-body d-flex align-items-center justify-content-center">
                <span><i class="bi bi-plus-lg"></i> <?= lang('Dashboard.newSession') ?></span>
            </div>
        </a>
    </div>
</div>

<h5><?= lang('Dashboard.recentSessions') ?></h5>
<?php if (empty($recentSessions)): ?>
    <div class="alert alert-info">
        <?= lang('Dashboard.noSessions') ?> <a href="/sessions/create"><?= lang('Dashboard.recordFirst') ?></a>.
    </div>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($recentSessions as $session): ?>
        <a href="/sessions/<?= $session['id'] ?>" class="list-group-item list-group-item-action">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= esc($session['session_date']) ?></strong>
                    <br>
                    <small class="text-muted">
                        <?= esc($session['weapon_name']) ?> &middot; <?= esc($session['distance']) ?>
                    </small>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
