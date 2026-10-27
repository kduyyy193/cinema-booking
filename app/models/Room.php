<?php
declare(strict_types=1);

class Room
{
    public static function byCinema(int $cinemaId): array
    {
        $st = getPDO()->prepare('SELECT r.*, (SELECT COUNT(*) FROM seats se WHERE se.room_id = r.id) AS seat_count FROM rooms r WHERE r.cinema_id = ? ORDER BY r.name');
        $st->execute([$cinemaId]);
        return $st->fetchAll();
    }

    public static function activeGroupedByCinema(): array
    {
        $rows = getPDO()->query("SELECT r.id, r.name, c.name AS cinema_name FROM rooms r JOIN cinemas c ON c.id = r.cinema_id
            WHERE r.status = 'active' AND c.status = 'active' ORDER BY c.name, r.name")->fetchAll();
        $groups = [];
        foreach ($rows as $r) $groups[$r['cinema_name']][] = $r;
        return $groups;
    }

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare('SELECT r.*, c.name AS cinema_name FROM rooms r JOIN cinemas c ON c.id = r.cinema_id WHERE r.id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function validate(array $in, bool $creating): array
    {
        $errors = [];
        $name = trim((string)($in['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 100) $errors['name'] = 'Tên phòng bắt buộc, tối đa 100 ký tự.';
        $rows = intOrZero($in['rows'] ?? '');
        $cols = intOrZero($in['cols'] ?? '');
        $vip = trim((string)($in['vip_rows'] ?? '0'));
        if ($rows < 1 || $rows > 26) $errors['rows'] = 'Số hàng từ 1 đến 26.';
        if ($cols < 1 || $cols > 30) $errors['cols'] = 'Số cột từ 1 đến 30.';
        if (!preg_match('/^\d+$/', $vip) || (int)$vip > $rows) $errors['vip_rows'] = 'Số hàng VIP từ 0 đến số hàng.';
        $status = (string)($in['status'] ?? 'active');
        if (!$creating && !in_array($status, ['active', 'inactive'], true)) $errors['status'] = 'Trạng thái không hợp lệ.';
        return [$errors, ['name' => $name, 'rows' => $rows, 'cols' => $cols, 'vip_rows' => (int)$vip, 'status' => $status]];
    }

    public static function create(array $data): int
    {
        return withTransaction(function () use ($data): int {
            $st = getPDO()->prepare('INSERT INTO rooms (cinema_id, name) VALUES (?, ?)');
            $st->execute([$data['cinema_id'], $data['name']]);
            $id = (int)getPDO()->lastInsertId();
            self::generateSeats($id, $data['rows'], $data['cols'], $data['vip_rows']);
            return $id;
        });
    }

    public static function hasBookings(int $roomId): bool
    {
        $st = getPDO()->prepare('SELECT COUNT(*) FROM booking_seats bs JOIN seats se ON se.id = bs.seat_id WHERE se.room_id = ?');
        $st->execute([$roomId]);
        return (int)$st->fetchColumn() > 0;
    }

    public static function update(int $id, array $data): void
    {
        withTransaction(function () use ($id, $data): void {
            $summary = self::seatSummary($id);
            $resize = isset($data['rows'], $data['cols'])
                && ($data['rows'] !== $summary['rows'] || $data['cols'] !== $summary['cols']);
            if ($resize && self::hasBookings($id)) {
                throw new InvalidArgumentException('Phòng đã có lịch sử đặt vé: không đổi được số hàng hoặc cột.');
            }
            $st = getPDO()->prepare('UPDATE rooms SET name = ?, status = ? WHERE id = ?');
            $st->execute([$data['name'], $data['status'], $id]);
            if ($resize) {
                getPDO()->prepare('DELETE FROM seats WHERE room_id = ?')->execute([$id]);
                self::generateSeats($id, $data['rows'], $data['cols'], (int)($data['vip_rows'] ?? 0));
            }
        });
    }

    public static function generateSeats(int $roomId, int $rows, int $cols, int $vipRows = 0): void
    {
        withTransaction(function () use ($roomId, $rows, $cols, $vipRows): void {
            $st = getPDO()->prepare('INSERT INTO seats (room_id, seat_row, seat_number, seat_type) VALUES (?, ?, ?, ?)');
            for ($r = 0; $r < $rows; $r++) {
                $type = $r >= $rows - $vipRows ? 'vip' : 'standard';
                for ($n = 1; $n <= $cols; $n++) {
                    $st->execute([$roomId, chr(65 + $r), $n, $type]);
                }
            }
        });
    }

    public static function seatSummary(int $roomId): array
    {
        $st = getPDO()->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(seat_type = 'vip'), 0) AS vip, COALESCE(SUM(status = 'locked'), 0) AS locked,
                COUNT(DISTINCT seat_row) AS row_count, COALESCE(MAX(seat_number), 0) AS cols FROM seats WHERE room_id = ?");
        $st->execute([$roomId]);
        $r = $st->fetch();
        return ['total' => (int)$r['total'], 'vip' => (int)$r['vip'], 'locked' => (int)$r['locked'], 'rows' => (int)$r['row_count'], 'cols' => (int)$r['cols']];
    }
}
