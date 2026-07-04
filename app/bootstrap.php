<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Autoloader;
use App\Core\Config;
use App\Core\ExceptionHandler;
use App\Core\Router;
use App\Core\Session;

$rootPath = dirname(__DIR__);
$composerAutoload = $rootPath . '/vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    require_once $rootPath . '/app/Core/Autoloader.php';
    Autoloader::register($rootPath);
    require_once $rootPath . '/app/Helpers/functions.php';
}

if (!function_exists('app_config')) {
    require_once $rootPath . '/app/Helpers/functions.php';
}

$config = Config::load($rootPath);
ExceptionHandler::register($config);

try {
    Application::instance();
} catch (RuntimeException $exception) {
    new Application($rootPath, $config, new Router());
}

Session::start($config['session'] ?? []);

return Application::instance();