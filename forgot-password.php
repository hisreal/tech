<?php
require_once __DIR__ . '/includes/helpers/auth.php';
$flashMessages = sms_flash();
$portal = preg_replace('/[^a-z]/', '', (string) ($_GET['portal'] ?? $_POST['portal'] ?? 'admin'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/auth-login.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-layout justify-content-center">
            <section class="auth-card-wrap" aria-label="Forgot password form">
                <div class="auth-card">
                    <h2>Forgot Password</h2>
                    <p class="welcome">Enter your username or email. A reset token will be prepared for future email delivery.</p>
                    <?php foreach ($flashMessages as $type => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <form action="forgot-password-handler.php" method="post">
                        <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                        <input type="hidden" name="portal" value="<?php echo sms_e($portal); ?>">
                        <div class="auth-field">
                            <label for="identifier">Username or Email</label>
                            <div class="auth-input">
                                <input id="identifier" name="identifier" type="text" required autocomplete="username">
                            </div>
                        </div>
                        <button class="auth-submit" type="submit">Create Reset Request</button>
                    </form>
                    <p class="auth-meta"><a href="<?php echo sms_e(in_array($portal, ['teacher','student','accountant'], true) ? $portal . '/login.php' : 'admin/login.php'); ?>">Back to login</a></p>
                </div>
            </section>
        </section>
    </main>
</body>
</html>