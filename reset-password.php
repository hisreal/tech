<?php
require_once __DIR__ . '/includes/helpers/auth.php';
$token = (string) ($_GET['token'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="mx-auto bg-white border rounded-3 p-4" style="max-width: 560px;">
            <h1 class="h4">Reset Password Placeholder</h1>
            <p class="text-muted">A secure reset token has been generated and stored in the database. Email delivery and final password update handling can be connected in the next backend phase.</p>
            <?php if ($token !== ''): ?>
                <label class="form-label">Development token preview</label>
                <input class="form-control" readonly value="<?php echo sms_e($token); ?>">
            <?php endif; ?>
            <a class="btn btn-success mt-3" href="login.php">Back to Login</a>
        </div>
    </main>
</body>
</html>