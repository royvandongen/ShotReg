<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Shotr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <style>
        .card-thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; }
        .photo-grid img { max-width: 100%; cursor: pointer; border-radius: 8px; }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-crosshair"></i> Shotr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="bi bi-house"></i> <?= lang('App.dashboard') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/sessions">
                            <i class="bi bi-journal-text"></i> <?= lang('App.sessions') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/weapons">
                            <i class="bi bi-bullseye"></i> <?= lang('App.weapons') ?>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (session()->get('is_admin')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-gear"></i> <?= lang('App.admin') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/admin/users">
                                <i class="bi bi-people"></i> <?= lang('App.users') ?>
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/invites">
                                <i class="bi bi-envelope-plus"></i> <?= lang('Admin.invitesTitle') ?>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin/settings">
                                <i class="bi bi-sliders"></i> <?= lang('App.systemSettings') ?>
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/email">
                                <i class="bi bi-envelope"></i> <?= lang('Admin.emailSettingsTitle') ?>
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-translate"></i>
                            <?= service('request')->getLocale() === 'nl' ? 'NL' : 'EN' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="post" action="/locale/switch">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="locale" value="en">
                                    <button type="submit" class="dropdown-item <?= service('request')->getLocale() === 'en' ? 'active' : '' ?>">
                                        <?= lang('App.english') ?>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form method="post" action="/locale/switch">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="locale" value="nl">
                                    <button type="submit" class="dropdown-item <?= service('request')->getLocale() === 'nl' ? 'active' : '' ?>">
                                        <?= lang('App.dutch') ?>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= esc(ucfirst(session()->get('username'))) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile">
                                <i class="bi bi-person"></i> <?= lang('App.profile') ?>
                            </a></li>
                            <li><a class="dropdown-item" href="/settings">
                                <i class="bi bi-gear"></i> <?= lang('App.settings') ?>
                            </a></li>
                            <li><a class="dropdown-item" href="/auth/setup2fa">
                                <i class="bi bi-shield-lock"></i> <?= lang('App.twoFaSettings') ?>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout">
                                <i class="bi bi-box-arrow-right"></i> <?= lang('App.logout') ?>
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>

    <?php if (session()->get('is_admin')): ?>
    <footer class="mt-5 py-3 border-top">
        <div class="container text-center text-muted small">
            Shotr &mdash; <?= esc(getenv('APP_VERSION') ?: 'dev') ?>
        </div>
    </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
