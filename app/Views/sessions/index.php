<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('App.sessions') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?= lang('Sessions.title') ?></h3>
    <a href="/sessions/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> <?= lang('Sessions.newSession') ?>
    </a>
</div>

<?php if (empty($sessions)): ?>
    <div class="alert alert-info">
        <?= lang('Sessions.noSessions') ?> <a href="/sessions/create"><?= lang('Sessions.recordFirst') ?></a>.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($sessions as $session): ?>
        <div class="col-12 col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex gap-3">
                        <?php if ($session['first_thumb']): ?>
                            <img src="/photos/thumb/<?= esc($session['first_thumb']) ?>"
                                 class="card-thumbnail" alt="Shooting card">
                        <?php else: ?>
                            <div class="card-thumbnail bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-1"><?= esc($session['session_date']) ?></h6>
                            <small class="text-muted">
                                <?= esc($session['weapon_name']) ?>
                                <br><?= esc($session['distance']) ?>
                                <?php if (! empty($session['location_name'])): ?>
                                    &middot; <?= esc($session['location_name']) ?>
                                <?php endif; ?>
                                <?php if ($session['photo_count'] > 0): ?>
                                    &middot; <?= $session['photo_count'] ?> photo<?= $session['photo_count'] > 1 ? 's' : '' ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="/sessions/<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> <?= lang('App.view') ?>
                    </a>
                    <a href="/sessions/edit/<?= $session['id'] ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i> <?= lang('App.edit') ?>
                    </a>
                    <form method="post" action="/sessions/delete/<?= $session['id'] ?>"
                          class="d-inline" onsubmit="return confirm('<?= lang('Sessions.deleteConfirm') ?>')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
