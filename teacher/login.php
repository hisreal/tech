<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
$flashMessages = sms_flash();
$oldIdentifier = $_SESSION['_old_login_identifier'] ?? '';
unset($_SESSION['_old_login_identifier']);
$schoolName = 'Brighter Future Standard School';
$homeUrl = '../index.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Login | <?php echo sms_e($schoolName); ?></title>
    <link rel="shortcut icon" href="../assets/img/favicon.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/auth-login.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <header class="auth-topbar" aria-label="Login page header">
            <a class="auth-brand" href="<?php echo sms_e($homeUrl); ?>">
                <img src="../assets/img/logo/school-logo.png" alt="School Logo">
                <span><strong><?php echo sms_e($schoolName); ?></strong>School Management System</span>
            </a>
            <a class="auth-back" href="<?php echo sms_e($homeUrl); ?>"><i class="fa-solid fa-arrow-left"></i><span>Back</span></a>
        </header>

        <section class="auth-layout">
            <aside class="auth-hero" aria-label="School portal introduction">
                <span class="auth-eyebrow"><i class="fa-solid fa-chalkboard-user"></i> Teacher</span>
                <h1>Secure access to a smarter school portal.</h1>
                <p>Manage learning, records, payments, reports, and communication through a modern role-based system built for professional school operations.</p>
                <div class="auth-visual">
                    <div class="auth-visual-card"><img src="../assets/img/auth/auth-1.svg" alt="Education portal illustration"></div>
                    <div class="auth-stat-stack">
                        <div class="auth-stat"><i class="fa-solid fa-shield-halved"></i><strong>Secure</strong><span>Role based access</span></div>
                        <div class="auth-stat"><i class="fa-solid fa-chart-line"></i><strong>Live</strong><span>Ready for data</span></div>
                    </div>
                </div>
            </aside>

            <section class="auth-card-wrap" aria-label="Teacher Login form">
                <div class="auth-card">
                    <div class="auth-role-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <h2>Teacher Login</h2>
                    <p class="welcome">Welcome back. Enter your credentials to continue to the teacher dashboard.</p>
                    <?php foreach ($flashMessages as $type => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <div class="auth-alert" role="alert" aria-live="polite"></div>
                    <form action="teacher-auth.php" method="post" data-auth-form novalidate>
                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                        <div class="auth-field">
                            <label for="username-teacher">Username or Email</label>
                            <div class="auth-input">
                                <i class="fa-solid fa-user"></i>
                                <input id="username-teacher" name="username" type="text" autocomplete="username" placeholder="Enter username or email" required value="<?php echo sms_e($oldIdentifier); ?>">
                            </div>
                            <small class="field-error" data-error-for="username"></small>
                        </div>

                        <div class="auth-field">
                            <label for="password-teacher">Password</label>
                            <div class="auth-input">
                                <i class="fa-solid fa-lock"></i>
                                <input id="password-teacher" name="password" type="password" autocomplete="current-password" placeholder="Enter password" required minlength="6">
                                <button class="password-toggle" type="button" aria-label="Show password" aria-controls="password-teacher"><i class="fa-solid fa-eye"></i></button>
                            </div>
                            <small class="field-error" data-error-for="password"></small>
                        </div>

                        <div class="auth-row">
                            <label class="remember"><input type="checkbox" name="remember" value="1"> Remember Me</label>
                            <a class="forgot" href="../forgot-password.php?portal=teacher">Forgot Password?</a>
                        </div>

                        <button class="auth-submit" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
                    </form>
                    <div class="role-pill"><i class="fa-solid fa-chalkboard-user"></i> Teacher portal</div>
                    <p class="auth-meta">Secured with database authentication, sessions, remember-me tokens, and audit-ready logging.</p>
                </div>
            </section>
        </section>
    </main>
    <script src="../assets/js/auth-login.js"></script>
</body>
</html>