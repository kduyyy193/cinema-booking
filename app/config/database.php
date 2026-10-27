<?php
declare(strict_types=1);

function config(?string $key = null)
{
    static $cfg = null;
    $cfg ??= require __DIR__ . '/config.php';
    return $key === null ? $cfg : ($cfg[$key] ?? null);
}

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . config('db_path'), null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    return $pdo;
}

function nowString(): string
{
    return date('Y-m-d H:i:s');
}

function withTransaction(callable $work)
{
    $pdo = getPDO();
    if ($pdo->inTransaction()) {
        return $work();
    }
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $result = $work();
        $pdo->exec('COMMIT');
        return $result;
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        throw $ex;
    }
}
