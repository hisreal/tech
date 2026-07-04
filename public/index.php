<?php

declare(strict_types=1);

$app = require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/Routes/web.php';
$app->run();