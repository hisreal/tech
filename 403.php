<?php
http_response_code(403);
require_once __DIR__ . '/includes/helpers/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Access Denied</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <main class="container py-5 text-center">
        <h1 class="display-5 fw-bold">403</h1>
        <p class="lead">Access denied. Your account is not allowed to open this page.</p>
        <a class="btn btn-success" href="login.php">Go to Login</a>
    </main>
</body>
</html>