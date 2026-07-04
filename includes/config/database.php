<?php
/**
 * Shared legacy database connection factory.
 *
 * All legacy pages should obtain PDO through sms_db(). This factory reads
 * the centralized database settings loaded from app/Config/database.php
 * and the DB_* values in .env.
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
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        (int) ($db['port'] ?? 3306),
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
