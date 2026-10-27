<?php
declare(strict_types=1);

class Movie
{
    public static function featured(): ?array
    {
        $r = getPDO()->query("SELECT * FROM movies WHERE status='now_showing' ORDER BY release_date DESC LIMIT 1")->fetch();
        return $r ?: null;
    }

    public static function nowShowing(?string $date = null, int $limit = 12): array
    {
        if ($date) {
            $st = getPDO()->prepare("SELECT DISTINCT m.* FROM movies m JOIN showtimes s ON s.movie_id = m.id
                WHERE m.status='now_showing' AND s.status='scheduled' AND date(s.start_time) = ? AND s.start_time > ?
                ORDER BY m.title LIMIT ?");
            $st->execute([$date, nowString(), $limit]);
        } else {
            $st = getPDO()->prepare("SELECT * FROM movies WHERE status='now_showing' ORDER BY release_date DESC LIMIT ?");
            $st->execute([$limit]);
        }
        return $st->fetchAll();
    }

    public static function upcoming(int $limit = 8): array
    {
        $st = getPDO()->prepare("SELECT * FROM movies WHERE status='upcoming' ORDER BY release_date LIMIT ?");
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare("SELECT * FROM movies WHERE id = ? AND status != 'hidden'");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findAny(int $id): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM movies WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function genresOf(int $id): array
    {
        $st = getPDO()->prepare('SELECT g.* FROM genres g JOIN movie_genres mg ON mg.genre_id = g.id WHERE mg.movie_id = ? ORDER BY g.name');
        $st->execute([$id]);
        return $st->fetchAll();
    }

    public static function genreIdsOf(int $id): array
    {
        return array_map(fn($g) => (int)$g['id'], self::genresOf($id));
    }

    public static function genresWithCount(): array
    {
        return getPDO()->query("SELECT g.id, g.name, COUNT(m.id) AS n FROM genres g
            LEFT JOIN movie_genres mg ON mg.genre_id = g.id
            LEFT JOIN movies m ON m.id = mg.movie_id AND m.status != 'hidden'
            GROUP BY g.id ORDER BY g.name")->fetchAll();
    }

    public static function allVisible(): array
    {
        return getPDO()->query("SELECT id, title FROM movies WHERE status != 'hidden' ORDER BY title")->fetchAll();
    }

    public static function allTitles(): array
    {
        return getPDO()->query('SELECT id, title FROM movies ORDER BY title')->fetchAll();
    }

    public static function search(string $q, int $genreId, string $status, int $page, int $perPage = 12): array
    {
        $where = ["m.status != 'hidden'"]; $args = [];
        if ($q !== '')       { $where[] = 'm.title LIKE ?'; $args[] = '%' . $q . '%'; }
        if ($status !== '')  { $where[] = 'm.status = ?';   $args[] = $status; }
        if ($genreId > 0)    { $where[] = 'EXISTS (SELECT 1 FROM movie_genres mg WHERE mg.movie_id = m.id AND mg.genre_id = ?)'; $args[] = $genreId; }
        $w = implode(' AND ', $where);

        $c = getPDO()->prepare("SELECT COUNT(*) FROM movies m WHERE $w");
        $c->execute($args);
        $pg = paginate((int)$c->fetchColumn(), $page, $perPage);

        $st = getPDO()->prepare("SELECT m.* FROM movies m WHERE $w ORDER BY m.release_date DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
        $st->execute($args);
        return [$st->fetchAll(), $pg];
    }

    public static function upcomingShowtimes(int $movieId): array
    {
        return Showtime::forMovie($movieId);
    }

    public static function adminList(array $filters, int $page): array
    {
        $where = ['1=1'];
        $args = [];
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') { $where[] = 'm.title LIKE ?'; $args[] = '%' . $q . '%'; }
        if (in_array($filters['status'] ?? '', ['now_showing', 'upcoming', 'hidden'], true)) { $where[] = 'm.status = ?'; $args[] = $filters['status']; }
        if ((int)($filters['genre_id'] ?? 0) > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM movie_genres mg WHERE mg.movie_id = m.id AND mg.genre_id = ?)';
            $args[] = (int)$filters['genre_id'];
        }
        $w = implode(' AND ', $where);

        $c = getPDO()->prepare("SELECT COUNT(*) FROM movies m WHERE $w");
        $c->execute($args);
        $pg = paginate((int)$c->fetchColumn(), $page, 10);

        $st = getPDO()->prepare("SELECT m.*, (SELECT GROUP_CONCAT(g.name, ', ') FROM genres g JOIN movie_genres mg ON mg.genre_id = g.id WHERE mg.movie_id = m.id) AS genre_names
            FROM movies m WHERE $w ORDER BY m.id DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}");
        $st->execute($args);
        return [$st->fetchAll(), $pg];
    }

    public static function create(array $data): int
    {
        $st = getPDO()->prepare('INSERT INTO movies (title, description, poster, trailer_url, director, cast_list, age_rating, duration, release_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $st->execute([
            $data['title'], $data['description'], $data['poster'] ?? null, $data['trailer_url'], $data['director'], $data['cast_list'],
            $data['age_rating'], $data['duration'], $data['release_date'], $data['status'], nowString(),
        ]);
        return (int)getPDO()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $st = getPDO()->prepare('UPDATE movies SET title = ?, description = ?, trailer_url = ?, director = ?, cast_list = ?,
                age_rating = ?, duration = ?, release_date = ?, status = ? WHERE id = ?');
        $st->execute([
            $data['title'], $data['description'], $data['trailer_url'], $data['director'], $data['cast_list'],
            $data['age_rating'], $data['duration'], $data['release_date'], $data['status'], $id,
        ]);
        if (!empty($data['poster'])) {
            getPDO()->prepare('UPDATE movies SET poster = ? WHERE id = ?')->execute([$data['poster'], $id]);
        }
    }

    public static function syncGenres(int $movieId, array $genreIds): void
    {
        getPDO()->prepare('DELETE FROM movie_genres WHERE movie_id = ?')->execute([$movieId]);
        $st = getPDO()->prepare('INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)');
        foreach (array_unique($genreIds) as $gid) $st->execute([$movieId, $gid]);
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['now_showing', 'upcoming', 'hidden'], true)) throw new InvalidArgumentException('Trạng thái phim không hợp lệ.');
        getPDO()->prepare('UPDATE movies SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public static function hasShowtimes(int $id): bool
    {
        $st = getPDO()->prepare('SELECT COUNT(*) FROM showtimes WHERE movie_id = ?');
        $st->execute([$id]);
        return (int)$st->fetchColumn() > 0;
    }

    public static function delete(int $id): void
    {
        if (self::hasShowtimes($id)) {
            throw new InvalidArgumentException('Phim đã có suất chiếu nên không thể xóa. Hãy ẩn phim thay vì xóa.');
        }
        withTransaction(function () use ($id): void {
            getPDO()->prepare('DELETE FROM movie_genres WHERE movie_id = ?')->execute([$id]);
            getPDO()->prepare('DELETE FROM movies WHERE id = ?')->execute([$id]);
        });
    }
}
