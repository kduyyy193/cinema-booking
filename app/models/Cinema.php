<?php
declare(strict_types=1);

class Cinema
{
    public static function all(): array
    {
        return getPDO()->query("SELECT * FROM cinemas WHERE status = 'active' ORDER BY name")->fetchAll();
    }

    public static function allIncludingInactive(string $q = ''): array
    {
        if ($q === '') {
            return getPDO()->query('SELECT * FROM cinemas ORDER BY name')->fetchAll();
        }
        $st = getPDO()->prepare('SELECT * FROM cinemas WHERE name LIKE ? ORDER BY name');
        $st->execute(['%' . $q . '%']);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM cinemas WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $st = getPDO()->prepare('INSERT INTO cinemas (name, address, phone) VALUES (?, ?, ?)');
        $st->execute([$data['name'], $data['address'], $data['phone']]);
        return (int)getPDO()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $st = getPDO()->prepare('UPDATE cinemas SET name = ?, address = ?, phone = ? WHERE id = ?');
        $st->execute([$data['name'], $data['address'], $data['phone'], $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw new InvalidArgumentException('Trạng thái rạp không hợp lệ.');
        }
        $st = getPDO()->prepare('UPDATE cinemas SET status = ? WHERE id = ?');
        $st->execute([$status, $id]);
    }

    public static function validate(array $in): array
    {
        $errors = [];
        $name = trim((string)($in['name'] ?? ''));
        $address = trim((string)($in['address'] ?? ''));
        $phone = trim((string)($in['phone'] ?? ''));
        if ($name === '' || mb_strlen($name) > 100) $errors['name'] = 'Tên rạp bắt buộc, tối đa 100 ký tự.';
        if (mb_strlen($address) > 200) $errors['address'] = 'Địa chỉ tối đa 200 ký tự.';
        if ($phone !== '' && (!preg_match('/^[0-9 +]+$/', $phone) || strlen($phone) < 8 || strlen($phone) > 15)) {
            $errors['phone'] = 'Điện thoại chỉ gồm số, dấu cách, dấu +, dài 8–15 ký tự.';
        }
        return [$errors, ['name' => $name, 'address' => $address, 'phone' => $phone]];
    }
}
