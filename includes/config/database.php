<?php
/**
 * Shared database connection factory.
 *
 * The UI is currently placeholder-driven. When schema work begins, all pages
 * should obtain PDO through sms_db() instead of creating local connections.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../helpers/functions.php';

function sms_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = sms_config('database');
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['name'],
        $db['charset']
    );

    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
