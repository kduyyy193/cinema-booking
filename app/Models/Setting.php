<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Setting
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $st = getPDO()->prepare('SELECT value FROM settings WHERE key = ?');
        $st->execute([$key]);
        $value = $st->fetchColumn();
        return $value === false ? $default : (string)$value;
    }

    public static function set(string $key, string $value): void
    {
        $st = getPDO()->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
        $st->execute([$key, $value]);
    }

    public static function all(): array
    {
        return getPDO()->query('SELECT key, value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public static function int(string $key, int $default): int
    {
        $v = self::get($key, (string)$default);
        return is_numeric($v) ? (int)$v : $default;
    }
}
