<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Backend Foundation') ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>">
</head>
<body class="bg-light">
    <main class="container py-5">
        <h1 class="h3 mb-3"><?= e($title ?? 'Backend Foundation') ?></h1>
        <p class="text-muted">The reusable PHP MVC backend foundation is installed and ready for module integration.</p>
        <ul class="list-group">
            <?php foreach (($modules ?? []) as $module): ?>
                <li class="list-group-item"><?= e($module) ?></li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>
