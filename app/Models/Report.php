<?php
declare(strict_types=1);

namespace App\Models;

class Report
{
    private const TICKETS_SQL = '(SELECT COUNT(*) FROM booking_seats bs WHERE bs.booking_id = b.id)';

    private static function scalar(string $sql, array $args = []): int
    {
        $st = getPDO()->prepare($sql);
        $st->execute($args);
        return (int)$st->fetchColumn();
    }

    public static function totals(): array
    {
        $today = date('Y-m-d');
        $month = date('Y-m');
        return [
            'movies'           => self::scalar("SELECT COUNT(*) FROM movies WHERE status != 'hidden'"),
            'upcoming_shows'   => self::scalar("SELECT COUNT(*) FROM showtimes WHERE status = 'scheduled' AND start_time > ?", [nowString()]),
            'bookings'         => self::scalar('SELECT COUNT(*) FROM bookings'),
            'confirmed'        => self::scalar("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'"),
            'revenue_today'    => self::scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND date(paid_at) = ?", [$today]),
            'revenue_month'    => self::scalar("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success' AND substr(paid_at, 1, 7) = ?", [$month]),
        ];
    }

    public static function summary(string $from, string $to): array
    {
        $st = getPDO()->prepare("SELECT COALESCE(SUM(p.amount), 0) AS revenue, COUNT(DISTINCT b.id) AS orders,
                COALESCE(SUM(" . self::TICKETS_SQL . "), 0) AS tickets
            FROM payments p JOIN bookings b ON b.id = p.booking_id
            WHERE p.status = 'success' AND date(p.paid_at) BETWEEN ? AND ?");
        $st->execute([$from, $to]);
        $r = $st->fetch();
        return ['revenue' => (int)$r['revenue'], 'orders' => (int)$r['orders'], 'tickets' => (int)$r['tickets']];
    }

    public static function revenueByDate(string $from, string $to): array
    {
        $st = getPDO()->prepare("SELECT date(p.paid_at) AS day, SUM(p.amount) AS revenue, COUNT(DISTINCT b.id) AS orders
            FROM payments p JOIN bookings b ON b.id = p.booking_id
            WHERE p.status = 'success' AND date(p.paid_at) BETWEEN ? AND ? GROUP BY day");
        $st->execute([$from, $to]);
        $found = [];
        foreach ($st->fetchAll() as $row) $found[$row['day']] = $row;

        $rows = [];
        for ($t = strtotime($from); $t <= strtotime($to); $t = strtotime('+1 day', $t)) {
            $day = date('Y-m-d', $t);
            $rows[] = ['day' => $day, 'revenue' => (int)($found[$day]['revenue'] ?? 0), 'orders' => (int)($found[$day]['orders'] ?? 0)];
        }
        return $rows;
    }

    public static function revenueByMovie(string $from, string $to): array
    {
        $st = getPDO()->prepare("SELECT m.id, m.title AS name, SUM(p.amount) AS revenue, COUNT(DISTINCT b.id) AS orders,
                SUM(" . self::TICKETS_SQL . ") AS tickets
            FROM payments p JOIN bookings b ON b.id = p.booking_id JOIN showtimes s ON s.id = b.showtime_id JOIN movies m ON m.id = s.movie_id
            WHERE p.status = 'success' AND date(p.paid_at) BETWEEN ? AND ? GROUP BY m.id ORDER BY revenue DESC, m.title");
        $st->execute([$from, $to]);
        return $st->fetchAll();
    }

    public static function revenueByCinema(string $from, string $to): array
    {
        $st = getPDO()->prepare("SELECT c.id, c.name AS name, SUM(p.amount) AS revenue, COUNT(DISTINCT b.id) AS orders,
                SUM(" . self::TICKETS_SQL . ") AS tickets
            FROM payments p JOIN bookings b ON b.id = p.booking_id JOIN showtimes s ON s.id = b.showtime_id
                JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
            WHERE p.status = 'success' AND date(p.paid_at) BETWEEN ? AND ? GROUP BY c.id ORDER BY revenue DESC, c.name");
        $st->execute([$from, $to]);
        return $st->fetchAll();
    }

    public static function popularMovies(int $limit = 5): array
    {
        $st = getPDO()->prepare("SELECT m.id, m.title, COUNT(bs.id) AS tickets,
                COALESCE((SELECT SUM(p.amount) FROM payments p JOIN bookings b2 ON b2.id = p.booking_id JOIN showtimes s2 ON s2.id = b2.showtime_id
                    WHERE s2.movie_id = m.id AND p.status = 'success'), 0) AS revenue
            FROM movies m JOIN showtimes s ON s.movie_id = m.id
                JOIN bookings b ON b.showtime_id = s.id AND b.status = 'confirmed'
                JOIN booking_seats bs ON bs.booking_id = b.id
            GROUP BY m.id ORDER BY tickets DESC, m.title LIMIT ?");
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public static function recentBookings(int $limit = 5): array
    {
        $st = getPDO()->prepare("SELECT b.id, b.booking_code, b.total_amount, b.status, u.name AS user_name, m.title AS movie_title
            FROM bookings b JOIN users u ON u.id = b.user_id JOIN showtimes s ON s.id = b.showtime_id JOIN movies m ON m.id = s.movie_id
            ORDER BY b.created_at DESC, b.id DESC LIMIT ?");
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public static function upcomingShowtimes(int $limit = 5): array
    {
        $st = getPDO()->prepare("SELECT s.id, s.start_time, m.title AS movie_title, c.name AS cinema_name, r.name AS room_name,
                (SELECT COUNT(*) FROM booking_seats bs WHERE bs.showtime_id = s.id AND bs.active = 1) AS sold,
                (SELECT COUNT(*) FROM seats se WHERE se.room_id = s.room_id AND se.status = 'active') AS capacity
            FROM showtimes s JOIN movies m ON m.id = s.movie_id JOIN rooms r ON r.id = s.room_id JOIN cinemas c ON c.id = r.cinema_id
            WHERE s.status = 'scheduled' AND s.start_time > ? ORDER BY s.start_time LIMIT ?");
        $st->execute([nowString(), $limit]);
        return $st->fetchAll();
    }

    public static function revenueLastDays(int $days = 7): array
    {
        return self::revenueByDate(date('Y-m-d', strtotime('-' . ($days - 1) . ' day')), date('Y-m-d'));
    }
}
