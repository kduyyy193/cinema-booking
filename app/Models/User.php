<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([strtolower($email)]);
        return $st->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function create(string $name, string $email, string $password): int
    {
        $st = getPDO()->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'customer', ?)");
        $st->execute([$name, strtolower($email), password_hash($password, PASSWORD_DEFAULT), nowString()]);
        return (int)getPDO()->lastInsertId();
    }

    public static function updateProfile(int $id, string $name, ?string $phone): void
    {
        $st = getPDO()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $st->execute([$name, $phone, $id]);
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        $st = getPDO()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $st->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    public static function adminList(array $filters, int $page): array
    {
        $where = ['1=1'];
        $args = [];
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(name LIKE ? OR email LIKE ?)';
            $args[] = '%' . $q . '%';
            $args[] = '%' . $q . '%';
        }
        if (in_array($filters['role'] ?? '', ['customer', 'admin'], true)) { $where[] = 'role = ?'; $args[] = $filters['role']; }
        if (in_array($filters['status'] ?? '', ['active', 'locked'], true)) { $where[] = 'status = ?'; $args[] = $filters['status']; }
        $w = implode(' AND ', $where);

        $c = getPDO()->prepare("SELECT COUNT(*) FROM users WHERE $w");
        $c->execute($args);
        $pg = paginate((int)$c->fetchColumn(), $page, 15);

        $st = getPDO()->prepare("SELECT id, name, email, phone, role, status, created_at FROM users WHERE $w ORDER BY id DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
        $st->execute($args);
        return [$st->fetchAll(), $pg];
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['active', 'locked'], true)) throw new InvalidArgumentException('Trạng thái không hợp lệ.');
        getPDO()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public static function setRole(int $id, string $role): void
    {
        if (!in_array($role, ['customer', 'admin'], true)) throw new InvalidArgumentException('Vai trò không hợp lệ.');
        getPDO()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
    }

    public static function countActiveAdmins(): int
    {
        return (int)getPDO()->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'")->fetchColumn();
    }
}
