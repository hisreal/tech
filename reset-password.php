<?php
require_once __DIR__ . '/includes/helpers/auth.php';

$token = (string) ($_GET['token'] ?? '');
$portal = preg_replace('/[^a-z]/', '', (string) ($_GET['portal'] ?? 'admin'));
$loginUrl = in_array($portal, ['teacher', 'student', 'accountant'], true) ? $portal . '/login.php' : 'admin/login.php';
$flashMessages = sms_flash();
$errors = $_SESSION['_reset_errors'] ?? [];
unset($_SESSION['_reset_errors']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth-login.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-layout justify-content-center">
            <section class="auth-card-wrap" aria-label="Reset password form">
                <div class="auth-card">
                    <h2>Reset Password</h2>
                    <?php if ($token === ''): ?>
                        <p class="welcome">This reset link is missing its token. Request a new one from the forgot password page.</p>
                        <a class="auth-submit d-inline-flex justify-content-center text-decoration-none mt-2" href="forgot-password.php?portal=<?php echo sms_e($portal); ?>">Request a New Link</a>
                    <?php else: ?>
                        <p class="welcome">Choose a new password for your account.</p>
                        <?php foreach ($flashMessages as $type => $messages): ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : sms_e($type); ?>" role="alert"><?php echo sms_e($message); ?></div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <form action="reset-password-handler.php" method="post">
                            <input type="hidden" name="_token" value="<?php echo sms_e(sms_csrf_token()); ?>">
                            <input type="hidden" name="token" value="<?php echo sms_e($token); ?>">
                            <input type="hidden" name="portal" value="<?php echo sms_e($portal); ?>">
                            <div class="auth-field">
                                <label for="password">New Password</label>
                                <div class="auth-input">
                                    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8">
                                </div>
                                <?php if (isset($errors['password'])): ?><small class="field-error"><?php echo sms_e($errors['password']); ?></small><?php endif; ?>
                            </div>
                            <div class="auth-field">
                                <label for="password_confirmation">Confirm New Password</label>
                                <div class="auth-input">
                                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8">
                                </div>
                                <?php if (isset($errors['password_confirmation'])): ?><small class="field-error"><?php echo sms_e($errors['password_confirmation']); ?></small><?php endif; ?>
                            </div>
                            <button class="auth-submit" type="submit">Reset Password</button>
                        </form>
                    <?php endif; ?>
                    <p class="auth-meta"><a href="<?php echo sms_e($loginUrl); ?>">Back to login</a></p>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
