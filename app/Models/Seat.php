<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

class Seat
{
    public static function byRoom(int $roomId): array
    {
        $st = getPDO()->prepare('SELECT * FROM seats WHERE room_id = ? ORDER BY seat_row, seat_number');
        $st->execute([$roomId]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM seats WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function updateType(int $id, string $type): void
    {
        if (!in_array($type, ['standard', 'vip'], true)) {
            throw new InvalidArgumentException('Loại ghế không hợp lệ.');
        }
        $st = getPDO()->prepare('UPDATE seats SET seat_type = ? WHERE id = ?');
        $st->execute([$type, $id]);
    }

    public static function hasActiveFutureBooking(int $id): bool
    {
        $st = getPDO()->prepare("SELECT COUNT(*) FROM booking_seats bs JOIN showtimes s ON s.id = bs.showtime_id
            WHERE bs.seat_id = ? AND bs.active = 1 AND s.start_time > ?");
        $st->execute([$id, nowString()]);
        return (int)$st->fetchColumn() > 0;
    }

    public static function setLocked(int $id, bool $locked): void
    {
        if ($locked && self::hasActiveFutureBooking($id)) {
            throw new InvalidArgumentException('Ghế đang được đặt ở suất chưa chiếu, không thể khóa.');
        }
        $st = getPDO()->prepare('UPDATE seats SET status = ? WHERE id = ?');
        $st->execute([$locked ? 'locked' : 'active', $id]);
    }
}
