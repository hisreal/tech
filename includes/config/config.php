<?php
/**
 * Central application configuration bridge for legacy pages.
 *
 * Database credentials are intentionally not stored here. The legacy
 * sms_config('database') value is loaded from the MVC application config,
 * which reads app/Config/database.php and the DB_* values in .env.
 */

require_once __DIR__ . '/../../app/bootstrap.php';

use App\Core\Application;

$app = Application::instance();

$smsConfig = [
    'app_name' => $app->config('app.name', 'School Management System'),
    'school_name' => 'Brighter Future Standard School, Katsina',
    'school_logo' => 'assets/img/logo/school-logo.png',
    'currency' => '₦',
    'academic_session' => '2025/2026',
    'term' => 'First Term',
    'theme' => [
        'primary' => '#0f766e',
        'primary_dark' => '#115e59',
    ],
    'database' => $app->config('database', []),
];
