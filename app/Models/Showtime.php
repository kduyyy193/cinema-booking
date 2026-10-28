<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use PDO;

class Showtime
{
    private const SEATS_LEFT_SQL = "(SELECT COUNT(*) FROM seats se WHERE se.room_id = s.room_id AND se.status = 'active')
        - (SELECT COUNT(*) FROM booking_seats bs WHERE bs.showtime_id = s.id AND bs.active = 1)";

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare("SELECT s.*, m.title AS movie_title, m.poster, m.duration, m.status AS movie_status,
                r.name AS room_name, r.status AS room_status, c.id AS cinema_id, c.name AS cinema_name, c.status AS cinema_status
            FROM showtimes s JOIN movies m ON m.id = s.movie_id JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
            WHERE s.id = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function isBookable(int $id): bool
    {
        $s = self::find($id);
        return $s !== null
            && $s['status'] === 'scheduled'
            && $s['start_time'] > nowString()
            && $s['room_status'] === 'active'
            && $s['cinema_status'] === 'active'
            && $s['movie_status'] !== 'hidden';
    }

    public static function availableSeatCount(int $id): int
    {
        $st = getPDO()->prepare('SELECT ' . self::SEATS_LEFT_SQL . ' FROM showtimes s WHERE s.id = ?');
        $st->execute([$id]);
        return max(0, (int)$st->fetchColumn());
    }

    public static function listForDate(string $date, int $movieId = 0, int $cinemaId = 0): array
    {
        $where = ['date(s.start_time) = ?', "m.status != 'hidden'", "c.status = 'active'", "r.status = 'active'"];
        $args = [$date];
        if ($movieId > 0)  { $where[] = 's.movie_id = ?'; $args[] = $movieId; }
        if ($cinemaId > 0) { $where[] = 'c.id = ?'; $args[] = $cinemaId; }
        $st = getPDO()->prepare("SELECT s.*, m.title AS movie_title, m.poster, m.duration, r.name AS room_name,
                c.id AS cinema_id, c.name AS cinema_name, " . self::SEATS_LEFT_SQL . " AS seats_left
            FROM showtimes s JOIN movies m ON m.id = s.movie_id JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
            WHERE " . implode(' AND ', $where) . ' ORDER BY s.start_time, m.title');
        $st->execute($args);
        return $st->fetchAll();
    }

    public static function upcoming(int $limit): array
    {
        $st = getPDO()->prepare("SELECT s.*, m.title AS movie_title, r.name AS room_name, c.name AS cinema_name, " . self::SEATS_LEFT_SQL . " AS seats_left
            FROM showtimes s JOIN movies m ON m.id = s.movie_id JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
            WHERE s.status = 'scheduled' AND s.start_time > ? AND m.status != 'hidden' AND r.status = 'active' AND c.status = 'active'
            ORDER BY s.start_time LIMIT ?");
        $st->execute([nowString(), $limit]);
        return $st->fetchAll();
    }

    public static function forMovie(int $movieId, string $date = ''): array
    {
        $where = ['s.movie_id = ?', "s.status = 'scheduled'", 's.start_time > ?', "r.status = 'active'", "c.status = 'active'"];
        $args = [$movieId, nowString()];
        if ($date !== '') { $where[] = 'date(s.start_time) = ?'; $args[] = $date; }
        $st = getPDO()->prepare("SELECT s.*, r.name AS room_name, c.id AS cinema_id, c.name AS cinema_name, " . self::SEATS_LEFT_SQL . " AS seats_left
            FROM showtimes s JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
            WHERE " . implode(' AND ', $where) . ' ORDER BY c.name, s.start_time');
        $st->execute($args);
        return $st->fetchAll();
    }

    public static function hasRoomConflict(int $roomId, string $start, string $end, int $ignoreId = 0): bool
    {
        $st = getPDO()->prepare("SELECT COUNT(*) FROM showtimes WHERE room_id = ? AND status = 'scheduled' AND id != ? AND start_time < ? AND end_time > ?");
        $st->execute([$roomId, $ignoreId, $end, $start]);
        return (int)$st->fetchColumn() > 0;
    }

    public static function adminList(array $filters, int $page): array
    {
        $where = ['1=1'];
        $args = [];
        if (($filters['date'] ?? '') !== '' && validDate((string)$filters['date'])) { $where[] = 'date(s.start_time) = ?'; $args[] = $filters['date']; }
        if ((int)($filters['movie_id'] ?? 0) > 0)  { $where[] = 's.movie_id = ?'; $args[] = (int)$filters['movie_id']; }
        if ((int)($filters['cinema_id'] ?? 0) > 0) { $where[] = 'c.id = ?'; $args[] = (int)$filters['cinema_id']; }
        if (in_array($filters['status'] ?? '', ['scheduled', 'cancelled'], true)) { $where[] = 's.status = ?'; $args[] = $filters['status']; }
        $w = implode(' AND ', $where);
        $from = 'FROM showtimes s JOIN movies m ON m.id = s.movie_id JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id';

        $c = getPDO()->prepare("SELECT COUNT(*) $from WHERE $w");
        $c->execute($args);
        $pg = paginate((int)$c->fetchColumn(), $page, 15);

        $st = getPDO()->prepare("SELECT s.*, m.title AS movie_title, r.name AS room_name, c.name AS cinema_name,
                (SELECT COUNT(*) FROM booking_seats bs WHERE bs.showtime_id = s.id AND bs.active = 1) AS sold
            $from WHERE $w ORDER BY s.start_time DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
        $st->execute($args);
        return [$st->fetchAll(), $pg];
    }

    public static function hasActiveSeats(int $id): bool
    {
        $st = getPDO()->prepare('SELECT COUNT(*) FROM booking_seats WHERE showtime_id = ? AND active = 1');
        $st->execute([$id]);
        return (int)$st->fetchColumn() > 0;
    }

    public static function validate(array $in, int $ignoreId = 0): array
    {
        $errors = [];
        $movieId = intOrZero($in['movie_id'] ?? '');
        $roomId = intOrZero($in['room_id'] ?? '');
        $startRaw = trim((string)($in['start_time'] ?? ''));
        $base = trim((string)($in['base_price'] ?? ''));
        $vip = trim((string)($in['vip_surcharge'] ?? ''));
        $existing = $ignoreId > 0 ? self::find($ignoreId) : null;

        $movie = null;
        if ($movieId > 0) {
            $st = getPDO()->prepare('SELECT * FROM movies WHERE id = ?');
            $st->execute([$movieId]);
            $movie = $st->fetch() ?: null;
        }
        if (!$movie) $errors['movie_id'] = 'Vui lòng chọn phim.';
        elseif ($movie['status'] === 'hidden') $errors['movie_id'] = 'Phim đang bị ẩn.';

        $room = null;
        if ($roomId > 0) {
            $st = getPDO()->prepare('SELECT r.*, c.status AS cinema_status FROM rooms r JOIN cinemas c ON c.id = r.cinema_id WHERE r.id = ?');
            $st->execute([$roomId]);
            $room = $st->fetch() ?: null;
        }
        if (!$room) $errors['room_id'] = 'Vui lòng chọn phòng chiếu.';
        elseif ($room['status'] !== 'active' || $room['cinema_status'] !== 'active') $errors['room_id'] = 'Phòng hoặc rạp đang ngừng hoạt động.';

        $start = null;
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $startRaw) ?: DateTime::createFromFormat('Y-m-d H:i:s', $startRaw);
        if ($dt === false) {
            $errors['start_time'] = 'Giờ bắt đầu không hợp lệ.';
        } else {
            $start = $dt->format('Y-m-d H:i:s');
            $unchangedStart = $existing && $existing['start_time'] === $start;
            if ($start <= nowString() && !$unchangedStart) $errors['start_time'] = 'Giờ bắt đầu phải ở tương lai.';
        }
        if (!preg_match('/^\d+$/', $base)) $errors['base_price'] = 'Giá vé là số nguyên không âm.';
        if (!preg_match('/^\d+$/', $vip))  $errors['vip_surcharge'] = 'Phụ thu VIP là số nguyên không âm.';

        $end = null;
        if (!$errors && $movie && $start) {
            $buffer = Setting::int('showtime_buffer_minutes', 15);
            $end = date('Y-m-d H:i:s', strtotime($start) + ((int)$movie['duration'] + $buffer) * 60);
            if (self::hasRoomConflict($roomId, $start, $end, $ignoreId)) {
                $errors['start_time'] = 'Phòng đã có suất chiếu trùng giờ.';
            }
        }

        if ($existing && self::hasActiveSeats($ignoreId)) {
            $changed = (int)$existing['movie_id'] !== $movieId
                || (int)$existing['room_id'] !== $roomId
                || ($start !== null && $existing['start_time'] !== $start);
            $priceErrors = array_intersect_key($errors, ['base_price' => 1, 'vip_surcharge' => 1]);
            $errors = $changed ? ['start_time' => 'Suất đã có người đặt: chỉ được đổi giá vé.'] + $priceErrors : $priceErrors;
        }

        return [$errors, [
            'movie_id' => $movieId, 'room_id' => $roomId, 'start_time' => $start, 'end_time' => $end,
            'base_price' => (int)$base, 'vip_surcharge' => (int)$vip,
        ]];
    }

    public static function create(array $data): int
    {
        $st = getPDO()->prepare('INSERT INTO showtimes (movie_id, room_id, start_time, end_time, base_price, vip_surcharge) VALUES (?, ?, ?, ?, ?, ?)');
        $st->execute([$data['movie_id'], $data['room_id'], $data['start_time'], $data['end_time'], $data['base_price'], $data['vip_surcharge']]);
        return (int)getPDO()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        if (self::hasActiveSeats($id)) {
            $st = getPDO()->prepare('UPDATE showtimes SET base_price = ?, vip_surcharge = ? WHERE id = ?');
            $st->execute([$data['base_price'], $data['vip_surcharge'], $id]);
            return;
        }
        $st = getPDO()->prepare('UPDATE showtimes SET movie_id = ?, room_id = ?, start_time = ?, end_time = ?, base_price = ?, vip_surcharge = ? WHERE id = ?');
        $st->execute([$data['movie_id'], $data['room_id'], $data['start_time'], $data['end_time'], $data['base_price'], $data['vip_surcharge'], $id]);
    }

    public static function cancel(int $id): void
    {
        withTransaction(function () use ($id): void {
            $st = getPDO()->prepare("UPDATE showtimes SET status = 'cancelled' WHERE id = ?");
            $st->execute([$id]);
            $ids = getPDO()->prepare("SELECT id FROM bookings WHERE showtime_id = ? AND status IN ('pending','confirmed')");
            $ids->execute([$id]);
            foreach ($ids->fetchAll(PDO::FETCH_COLUMN) as $bookingId) {
                Booking::cancel((int)$bookingId);
            }
        });
    }
}
