<?php
require_once __DIR__ . '/includes/helpers/auth.php';

$portals = [
    ['role' => 'admin', 'label' => 'Administrator', 'icon' => 'fa-user-shield', 'href' => 'admin/login.php'],
    ['role' => 'teacher', 'label' => 'Teacher', 'icon' => 'fa-chalkboard-user', 'href' => 'teacher/login.php'],
    ['role' => 'student', 'label' => 'Student', 'icon' => 'fa-user-graduate', 'href' => 'student/login.php'],
    ['role' => 'accountant', 'label' => 'Accountant', 'icon' => 'fa-file-invoice-dollar', 'href' => 'accountant/login.php'],
];
$schoolName = 'Brighter Future Standard School';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In | <?php echo sms_e($schoolName); ?></title>
    <link rel="shortcut icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth-login.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <header class="auth-topbar" aria-label="Login page header">
           
        </header>

        <section class="auth-layout justify-content-center">
            <section class="auth-card-wrap" aria-label="Choose your portal">
                <div class="auth-card">
                    <h2>Sign In</h2>
                    <p class="welcome">Choose your portal to continue.</p>
                    <div class="d-grid gap-3 mt-3">
                        <?php foreach ($portals as $portal): ?>
                            <a class="auth-submit d-inline-flex align-items-center justify-content-center gap-2 text-decoration-none" href="<?php echo sms_e($portal['href']); ?>">
                                <i class="fa-solid <?php echo sms_e($portal['icon']); ?>"></i>
                                <?php echo sms_e($portal['label']); ?> Login
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
