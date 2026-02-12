<!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - ShotReg</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <h2>ShotReg</h2>
                    <div class="mt-2">
                        <form method="post" action="/locale/switch" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="locale" value="en">
                            <button type="submit" class="btn btn-sm <?= service('request')->getLocale() === 'en' ? 'btn-secondary' : 'btn-outline-secondary' ?>">EN</button>
                        </form>
                        <form method="post" action="/locale/switch" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="locale" value="nl">
                            <button type="submit" class="btn btn-sm <?= service('request')->getLocale() === 'nl' ? 'btn-secondary' : 'btn-outline-secondary' ?>">NL</button>
                        </form>
                    </div>
                </div>
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
