<?php
/**
 * Central application configuration.
 *
 * Keep environment-specific values here until a .env loader is introduced.
 * Pages and shared components should read from sms_config() instead of
 * hardcoding school identity, currency, or asset defaults.
 */

$smsConfig = [
    'app_name' => 'School Management System',
    'school_name' => 'Brighter Future Standard School, Katsina',
    'school_logo' => 'assets/img/logo/school-logo.png',
    'currency' => '₦',
    'academic_session' => '2025/2026',
    'term' => 'First Term',
    'theme' => [
        'primary' => '#0f766e',
        'primary_dark' => '#115e59',
    ],
    'database' => [
        'host' => '127.0.0.1',
        'name' => 'school_management',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
