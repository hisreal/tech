<?php
require_once __DIR__ . '/../includes/helpers/auth.php';
sms_auth()->logout();
header('Location: login.php');
exit;