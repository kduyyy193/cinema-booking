<?php
declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

class Booking
{
    public const MAX_SEATS = 8;
    public const MAX_PENDING_PER_USER = 3;
    private const PROCESSING_GRACE_SECONDS = 300;

    private const DETAIL_SQL = "SELECT b.*, m.title AS movie_title, m.poster, c.name AS cinema_name, r.name AS room_name,
            s.start_time, s.end_time, s.status AS showtime_status, u.name AS user_name, u.email AS user_email
        FROM bookings b
        JOIN showtimes s ON s.id = b.showtime_id JOIN movies m ON m.id = s.movie_id
        JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
        JOIN users u ON u.id = b.user_id";

    public static function seatMap(int $showtimeId): array
    {
        $st = getPDO()->prepare("SELECT se.id, se.seat_row, se.seat_number, se.seat_type, se.status,
                s.base_price + CASE WHEN se.seat_type = 'vip' THEN s.vip_surcharge ELSE 0 END AS price,
                EXISTS (SELECT 1 FROM booking_seats bs WHERE bs.showtime_id = s.id AND bs.seat_id = se.id AND bs.active = 1) AS taken
            FROM showtimes s JOIN seats se ON se.room_id = s.room_id
            WHERE s.id = ? ORDER BY se.seat_row, se.seat_number");
        $st->execute([$showtimeId]);
        $seats = [];
        foreach ($st->fetchAll() as $row) {
            $row['price'] = (int)$row['price'];
            $row['state'] = (int)$row['taken'] === 1 ? 'taken' : ($row['status'] === 'locked' ? 'locked' : 'free');
            $seats[] = $row;
        }
        return $seats;
    }

    public static function expireStale(): int
    {
        $pdo = getPDO();
        $now = nowString();
        $grace = date('Y-m-d H:i:s', time() - self::PROCESSING_GRACE_SECONDS);

        $probe = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'pending' AND expires_at < ?");
        $probe->execute([$now]);
        if ((int)$probe->fetchColumn() === 0) return 0;

        return withTransaction(function () use ($pdo, $now, $grace): int {
            $stuck = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE status = 'processing'
                AND booking_id IN (SELECT id FROM bookings WHERE status = 'pending' AND expires_at < ?)");
            $stuck->execute([$grace]);

            $find = $pdo->prepare("SELECT b.id FROM bookings b WHERE b.status = 'pending' AND b.expires_at < ?
                AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.booking_id = b.id AND p.status = 'processing')");
            $find->execute([$now]);
            $ids = $find->fetchAll(PDO::FETCH_COLUMN);

            $setBooking = $pdo->prepare("UPDATE bookings SET status = 'expired' WHERE id = ?");
            $freeSeats = $pdo->prepare('UPDATE booking_seats SET active = 0 WHERE booking_id = ?');
            $expirePay = $pdo->prepare("UPDATE payments SET status = 'expired' WHERE booking_id = ? AND status = 'pending'");
            foreach ($ids as $id) {
                $setBooking->execute([$id]);
                $freeSeats->execute([$id]);
                $expirePay->execute([$id]);
            }
            return count($ids);
        });
    }

    public static function validateSelection(int $showtimeId, array $seatIds): array
    {
        if (!$seatIds) throw new InvalidArgumentException('Vui lòng chọn ít nhất một ghế.');
        foreach ($seatIds as $id) {
            if (!is_int($id) || $id <= 0) throw new InvalidArgumentException('Danh sách ghế không hợp lệ.');
        }
        if (count($seatIds) !== count(array_unique($seatIds))) throw new InvalidArgumentException('Danh sách ghế bị trùng.');
        if (count($seatIds) > self::MAX_SEATS) throw new InvalidArgumentException('Mỗi đơn tối đa ' . self::MAX_SEATS . ' ghế.');

        $showtime = Showtime::find($showtimeId);
        if (!$showtime || !Showtime::isBookable($showtimeId)) throw new InvalidArgumentException('Suất chiếu không còn khả dụng.');

        $marks = implode(',', array_fill(0, count($seatIds), '?'));
        $st = getPDO()->prepare("SELECT se.id, se.seat_row, se.seat_number, se.seat_type, se.status,
                s.base_price + CASE WHEN se.seat_type = 'vip' THEN s.vip_surcharge ELSE 0 END AS price,
                EXISTS (SELECT 1 FROM booking_seats bs WHERE bs.showtime_id = s.id AND bs.seat_id = se.id AND bs.active = 1) AS taken
            FROM showtimes s JOIN seats se ON se.room_id = s.room_id
            WHERE s.id = ? AND se.id IN ($marks) ORDER BY se.seat_row, se.seat_number");
        $st->execute(array_merge([$showtimeId], $seatIds));
        $seats = $st->fetchAll();
        if (count($seats) !== count($seatIds)) throw new InvalidArgumentException('Có ghế không thuộc phòng chiếu của suất này.');

        $total = 0;
        foreach ($seats as &$seat) {
            if ($seat['status'] !== 'active') throw new InvalidArgumentException('Ghế ' . $seat['seat_row'] . $seat['seat_number'] . ' đang bị khóa.');
            if ((int)$seat['taken'] === 1) throw new SeatTakenException('Ghế ' . $seat['seat_row'] . $seat['seat_number'] . ' đã có người đặt.');
            $seat['price'] = (int)$seat['price'];
            $total += $seat['price'];
        }
        unset($seat);
        return ['showtime' => $showtime, 'seats' => $seats, 'total' => $total];
    }

    public static function create(int $userId, int $showtimeId, array $seatIds): array
    {
        $bookingId = withTransaction(function () use ($userId, $showtimeId, $seatIds): int {
            $pdo = getPDO();
            $selection = self::validateSelection($showtimeId, $seatIds);

            $count = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'pending' AND expires_at > ?");
            $count->execute([$userId, nowString()]);
            if ((int)$count->fetchColumn() >= self::MAX_PENDING_PER_USER) {
                throw new InvalidArgumentException('Bạn đang có ' . self::MAX_PENDING_PER_USER . ' đơn chờ thanh toán (tối đa). Hãy thanh toán hoặc hủy bớt.');
            }

            $expires = date('Y-m-d H:i:s', time() + Setting::int('hold_minutes', 10) * 60);
            $insert = $pdo->prepare("INSERT INTO bookings (user_id, showtime_id, booking_code, total_amount, status, expires_at, created_at)
                VALUES (?, ?, ?, ?, 'pending', ?, ?)");
            $id = 0;
            for ($attempt = 0; $attempt < 5 && $id === 0; $attempt++) {
                try {
                    $insert->execute([$userId, $showtimeId, self::generateCode(), $selection['total'], $expires, nowString()]);
                    $id = (int)$pdo->lastInsertId();
                } catch (PDOException $ex) {
                    if (!str_contains($ex->getMessage(), 'booking_code')) throw $ex;
                }
            }
            if ($id === 0) throw new RuntimeException('Không thể sinh mã đơn.');

            $line = $pdo->prepare('INSERT INTO booking_seats (booking_id, showtime_id, seat_id, price, active) VALUES (?, ?, ?, ?, 1)');
            try {
                foreach ($selection['seats'] as $seat) {
                    $line->execute([$id, $showtimeId, $seat['id'], $seat['price']]);
                }
            } catch (PDOException $ex) {
                if (str_contains($ex->getMessage(), 'UNIQUE constraint failed')) {
                    throw new SeatTakenException('Một số ghế vừa được người khác đặt.');
                }
                throw $ex;
            }
            return $id;
        });
        return self::find($bookingId);
    }

    private static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $suffix = '';
        for ($i = 0; $i < 6; $i++) $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        return 'CB' . date('ymd') . $suffix;
    }

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare(self::DETAIL_SQL . ' WHERE b.id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $st = getPDO()->prepare(self::DETAIL_SQL . ' WHERE b.booking_code = ?');
        $st->execute([$code]);
        return $st->fetch() ?: null;
    }

    public static function seats(int $bookingId): array
    {
        $st = getPDO()->prepare('SELECT se.seat_row, se.seat_number, se.seat_type, bs.price
            FROM booking_seats bs JOIN seats se ON se.id = bs.seat_id
            WHERE bs.booking_id = ? ORDER BY se.seat_row, se.seat_number');
        $st->execute([$bookingId]);
        return $st->fetchAll();
    }

    public static function seatLabels(int $bookingId): string
    {
        return implode(', ', array_map(fn($s) => $s['seat_row'] . $s['seat_number'], self::seats($bookingId)));
    }

    private static function buildFilters(array $filters, array &$where, array &$args): void
    {
        if (in_array($filters['status'] ?? '', ['pending', 'confirmed', 'cancelled', 'expired'], true)) {
            $where[] = 'b.status = ?';
            $args[] = $filters['status'];
        }
        if (($filters['date_from'] ?? '') !== '' && validDate((string)$filters['date_from'])) {
            $where[] = 'date(b.created_at) >= ?';
            $args[] = $filters['date_from'];
        }
        if (($filters['date_to'] ?? '') !== '' && validDate((string)$filters['date_to'])) {
            $where[] = 'date(b.created_at) <= ?';
            $args[] = $filters['date_to'];
        }
    }

    public static function forUser(int $userId, array $filters, int $page): array
    {
        $where = ['b.user_id = ?'];
        $args = [$userId];
        self::buildFilters($filters, $where, $args);
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = 'b.booking_code LIKE ?';
            $args[] = '%' . $q . '%';
        }
        $w = implode(' AND ', $where);
        $from = 'FROM bookings b JOIN showtimes s ON s.id = b.showtime_id JOIN movies m ON m.id = s.movie_id
            JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id';

        $c = getPDO()->prepare("SELECT COUNT(*) $from WHERE $w");
        $c->execute($args);
        $pg = paginate((int)$c->fetchColumn(), $page, 6);

        $st = getPDO()->prepare("SELECT b.id, b.booking_code, b.total_amount, b.status, b.expires_at, b.created_at,
                m.title AS movie_title, m.poster, c.name AS cinema_name, r.name AS room_name, s.start_time,
                (SELECT COUNT(*) FROM booking_seats bs WHERE bs.booking_id = b.id) AS seat_count,
                (SELECT GROUP_CONCAT(se.seat_row || se.seat_number, ', ') FROM booking_seats bs JOIN seats se ON se.id = bs.seat_id WHERE bs.booking_id = b.id) AS seat_labels
            $from WHERE $w ORDER BY b.created_at DESC, b.id DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
        $st->execute($args);
        return [$st->fetchAll(), $pg];
    }

    public static function canCancel(array $booking): bool
    {
        return self::cancelBlockReason($booking) === null;
    }

    public static function cancelBlockReason(array $booking): ?string
    {
        switch ($booking['status']) {
            case 'pending':
                return null;
            case 'confirmed':
                $minutes = Setting::int('cancel_before_minutes', 120);
                if (strtotime((string)$booking['start_time']) - time() >= $minutes * 60) return null;
                return 'Đã quá hạn hủy vé: chỉ được hủy trước giờ chiếu ít nhất ' . $minutes . ' phút.';
            case 'cancelled':
                return 'Đơn này đã được hủy trước đó.';
            default:
                return 'Đơn này đã hết hạn nên không thể hủy.';
        }
    }

    public static function cancel(int $id): void
    {
        withTransaction(function () use ($id): void {
            $pdo = getPDO();
            $st = $pdo->prepare('SELECT status FROM bookings WHERE id = ?');
            $st->execute([$id]);
            $status = $st->fetchColumn();
            if (!in_array($status, ['pending', 'confirmed'], true)) {
                throw new InvalidArgumentException('Đơn không ở trạng thái có thể hủy.');
            }
            $pdo->prepare("UPDATE bookings SET status = 'cancelled', cancelled_at = ? WHERE id = ?")->execute([nowString(), $id]);
            $pdo->prepare('UPDATE booking_seats SET active = 0 WHERE booking_id = ?')->execute([$id]);
            $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE booking_id = ? AND status = 'success'")->execute([$id]);
            $pdo->prepare("UPDATE payments SET status = 'expired' WHERE booking_id = ? AND status IN ('pending','processing')")->execute([$id]);
        });
    }

    public static function adminList(array $filters, int $page): array
    {
        $where = ['1=1'];
        $args = [];
        self::buildFilters($filters, $where, $args);
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(b.booking_code LIKE ? OR u.email LIKE ?)';
            $args[] = '%' . $q . '%';
            $args[] = '%' . $q . '%';
        }
        if ((int)($filters['movie_id'] ?? 0) > 0) {
            $where[] = 's.movie_id = ?';
            $args[] = (int)$filters['movie_id'];
        }
        $w = implode(' AND ', $where);
        $from = 'FROM bookings b JOIN showtimes s ON s.id = b.showtime_id JOIN movies m ON m.id = s.movie_id JOIN users u ON u.id = b.user_id';

        $c = getPDO()->prepare("SELECT COUNT(*) $from WHERE $w");
        $c->execute($args);
        $pg = paginate((int)$c->fetchColumn(), $page, 15);

        $st = getPDO()->prepare("SELECT b.id, b.booking_code, b.total_amount, b.status, b.created_at,
                u.name AS user_name, u.email AS user_email, m.title AS movie_title, s.start_time,
                (SELECT p.status FROM payments p WHERE p.booking_id = b.id ORDER BY p.id DESC LIMIT 1) AS payment_status
            $from WHERE $w ORDER BY b.created_at DESC, b.id DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
        $st->execute($args);
        return [$st->fetchAll(), $pg];
    }

    public static function availableAdminActions(array $booking): array
    {
        return match ($booking['status']) {
            'pending'   => ['confirmed' => 'Xác nhận thanh toán tại quầy', 'cancelled' => 'Hủy đơn'],
            'confirmed' => ['cancelled' => 'Hủy đơn và hoàn tiền'],
            default     => [],
        };
    }

    public static function updateStatus(int $id, string $status): void
    {
        withTransaction(function () use ($id, $status): void {
            $booking = self::find($id);
            if (!$booking) throw new InvalidArgumentException('Không tìm thấy đơn.');
            $from = $booking['status'];

            if ($status === 'cancelled' && in_array($from, ['pending', 'confirmed'], true)) {
                self::cancel($id);
                return;
            }
            if ($status === 'confirmed' && $from === 'pending') {
                self::confirmAtCounter($booking);
                return;
            }
            throw new InvalidArgumentException('Không thể chuyển đơn từ "' . bookingStatusLabel($from) . '" sang "' . bookingStatusLabel($status) . '".');
        });
    }

    private static function confirmAtCounter(array $booking): void
    {
        if ($booking['expires_at'] < nowString()) {
            throw new InvalidArgumentException('Đơn đã quá hạn giữ ghế, không thể xác nhận.');
        }
        $pdo = getPDO();
        $id = (int)$booking['id'];
        $open = $pdo->prepare("SELECT id FROM payments WHERE booking_id = ? AND status IN ('pending','processing') ORDER BY id DESC LIMIT 1");
        $open->execute([$id]);
        $paymentId = $open->fetchColumn();
        if ($paymentId !== false) {
            $pdo->prepare("UPDATE payments SET status = 'success', method = 'counter', paid_at = ? WHERE id = ?")->execute([nowString(), $paymentId]);
            $pdo->prepare("UPDATE payments SET status = 'expired' WHERE booking_id = ? AND id != ? AND status IN ('pending','processing')")->execute([$id, $paymentId]);
        } else {
            $pdo->prepare("INSERT INTO payments (booking_id, transaction_code, amount, method, status, created_at, paid_at)
                VALUES (?, ?, ?, 'counter', 'success', ?, ?)")
                ->execute([$id, Payment::generateTransactionCode(), (int)$booking['total_amount'], nowString(), nowString()]);
        }
        $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?")->execute([$id]);
    }
}
