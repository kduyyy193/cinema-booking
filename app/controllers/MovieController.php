<?php
declare(strict_types=1);

class MovieController
{
    public static function home(): void
    {
        $days = nextDays();
        $date = pickDay($days, (string)($_GET['date'] ?? ''));

        render('home/index', [
            'pageTitle'  => 'Trang chủ',
            'featured'   => Movie::featured(),
            'days'       => $days,
            'date'       => $date,
            'nowShowing' => Movie::nowShowing($date),
            'upcoming'   => Movie::upcoming(4),
            'genres'     => Movie::genresWithCount(),
            'soon'       => Showtime::upcoming(6),
        ]);
    }

    public static function index(): void
    {
        [$movies, $pg] = Movie::search(
            trim($_GET['q'] ?? ''), (int)($_GET['genre'] ?? 0),
            in_array($_GET['status'] ?? '', ['now_showing', 'upcoming'], true) ? $_GET['status'] : '',
            max(1, (int)($_GET['page'] ?? 1))
        );
        render('movies/index', ['pageTitle' => 'Phim', 'movies' => $movies, 'pg' => $pg, 'genres' => Movie::genresWithCount()]);
    }

    public static function show(): void
    {
        $movie = Movie::find((int)($_GET['id'] ?? 0));
        if (!$movie) { renderError(404, 'Không tìm thấy phim.'); return; }

        $days = nextDays();
        $date = pickDay($days, (string)($_GET['date'] ?? ''));
        $byCinema = [];
        foreach (Showtime::forMovie((int)$movie['id'], $date) as $s) {
            $byCinema[$s['cinema_name']][] = $s;
        }
        render('movies/show', [
            'pageTitle' => $movie['title'], 'movie' => $movie,
            'genres' => Movie::genresOf((int)$movie['id']),
            'days' => $days, 'date' => $date, 'byCinema' => $byCinema,
        ]);
    }
}
