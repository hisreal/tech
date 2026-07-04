<?php

declare(strict_types=1);

use App\Core\Config;

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
|
| This is the single database configuration file for the application.
| For deployment, update the DB_* values in the project .env file:
|
|   DB_HOST="your-live-db-host"
|   DB_PORT=3306
|   DB_NAME="your-live-db-name"
|   DB_USER="your-live-db-user"
|   DB_PASS="your-live-db-password"
|   DB_CHARSET="utf8mb4"
|
| Do not hardcode database credentials in Models, Controllers, Services,
| Helpers, or views. They should all use App\Core\Database or sms_db(),
| which both read from this centralized configuration.
|
*/

return [
    'host' => Config::env('DB_HOST', '127.0.0.1'),
    'port' => (int) Config::env('DB_PORT', 3306),
    'name' => Config::env('DB_NAME', 'school_management'),
    'user' => Config::env('DB_USER', 'root'),
    'password' => Config::env('DB_PASS', ''),
    'charset' => Config::env('DB_CHARSET', 'utf8mb4'),
];
