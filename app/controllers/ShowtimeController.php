<?php
declare(strict_types=1);

class ShowtimeController
{
    public static function index(): void
    {
        $days = nextDays();
        $date = pickDay($days, (string)($_GET['date'] ?? ''));
        $movieId = intOrZero($_GET['movie'] ?? 0);
        $cinemaId = intOrZero($_GET['cinema'] ?? 0);

        $rows = Showtime::listForDate($date, $movieId, $cinemaId);
        $now = nowString();
        foreach ($rows as &$row) {
            if ($row['status'] === 'cancelled') $row['state'] = 'cancelled';
            elseif ($row['start_time'] <= $now) $row['state'] = 'finished';
            elseif ((int)$row['seats_left'] <= 0) $row['state'] = 'soldout';
            else $row['state'] = 'open';
        }
        unset($row);

        render('showtimes/index', [
            'pageTitle' => 'Lịch chiếu', 'days' => $days, 'date' => $date, 'movieId' => $movieId, 'cinemaId' => $cinemaId,
            'rows' => $rows, 'movies' => Movie::allVisible(), 'cinemas' => Cinema::all(),
        ]);
    }

    public static function select(): void
    {
        $movie = Movie::find(intOrZero($_GET['movie'] ?? 0));
        if (!$movie) { renderError(404, 'Không tìm thấy phim.'); return; }

        $days = nextDays();
        $date = pickDay($days, (string)($_GET['date'] ?? ''));
        $cinemas = Cinema::all();
        $cinemaId = intOrZero($_GET['cinema'] ?? 0);
        if (!in_array($cinemaId, array_map(fn($c) => (int)$c['id'], $cinemas), true)) {
            $cinemaId = $cinemas ? (int)$cinemas[0]['id'] : 0;
        }
        $showtimes = array_values(array_filter(
            Showtime::forMovie((int)$movie['id'], $date),
            fn($s) => (int)$s['cinema_id'] === $cinemaId
        ));

        render('booking/select-showtime', [
            'pageTitle' => 'Chọn suất chiếu', 'movie' => $movie, 'days' => $days, 'date' => $date,
            'cinemas' => $cinemas, 'cinemaId' => $cinemaId, 'showtimes' => $showtimes,
        ]);
    }
}
