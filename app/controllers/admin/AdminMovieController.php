<?php
declare(strict_types=1);

class AdminMovieController
{
    private static function emptyMovie(): array
    {
        return [
            'id' => 0, 'title' => '', 'description' => '', 'poster' => null, 'trailer_url' => '', 'director' => '', 'cast_list' => '',
            'age_rating' => 'P', 'duration' => '', 'release_date' => date('Y-m-d'), 'status' => 'now_showing',
        ];
    }

    private static function form(array $movie, array $errors, array $selected, string $title): void
    {
        render('admin/movies/form', [
            'pageTitle' => $title, 'movie' => $movie, 'errors' => $errors,
            'genres' => Genre::all(), 'selected' => $selected,
        ], 'admin');
    }

    public static function index(): void
    {
        requireRole('admin');
        $filters = [
            'q' => (string)($_GET['q'] ?? ''), 'status' => (string)($_GET['status'] ?? ''), 'genre_id' => intOrZero($_GET['genre_id'] ?? 0),
        ];
        [$rows, $pg] = Movie::adminList($filters, max(1, intOrZero($_GET['page'] ?? 1)));
        render('admin/movies/index', [
            'pageTitle' => 'Quản lý phim', 'rows' => $rows, 'pg' => $pg, 'filters' => $filters, 'genres' => Genre::all(),
        ], 'admin');
    }

    public static function create(): void
    {
        requireRole('admin');
        self::form(self::emptyMovie(), [], [], 'Thêm phim');
    }

    public static function store(): void
    {
        requireRole('admin');
        requireCsrf();
        [$errors, $data] = validateMovieData($_POST);
        $poster = null;
        if (!$errors) $poster = self::uploadPoster($errors);
        if ($errors) {
            self::form(array_merge(self::emptyMovie(), $_POST), $errors, $data['genre_ids'], 'Thêm phim');
            return;
        }
        try {
            $data['poster'] = $poster;
            withTransaction(function () use ($data): void {
                $id = Movie::create($data);
                Movie::syncGenres($id, $data['genre_ids']);
            });
        } catch (Throwable $ex) {
            deletePosterFile($poster);
            throw $ex;
        }
        setFlash('success', 'Đã thêm phim.');
        redirect('admin/movies');
    }

    public static function edit(): void
    {
        requireRole('admin');
        $movie = Movie::findAny(intOrZero($_GET['id'] ?? 0));
        if (!$movie) { renderError(404, 'Không tìm thấy phim.'); return; }
        self::form($movie, [], Movie::genreIdsOf((int)$movie['id']), 'Sửa phim');
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $movie = Movie::findAny(intOrZero($_POST['id'] ?? 0));
        if (!$movie) { renderError(404, 'Không tìm thấy phim.'); return; }

        [$errors, $data] = validateMovieData($_POST);
        $poster = null;
        if (!$errors) $poster = self::uploadPoster($errors);
        if ($errors) {
            self::form(array_merge($movie, $_POST, ['id' => $movie['id'], 'poster' => $movie['poster']]), $errors, $data['genre_ids'], 'Sửa phim');
            return;
        }
        try {
            $data['poster'] = $poster;
            withTransaction(function () use ($movie, $data): void {
                Movie::update((int)$movie['id'], $data);
                Movie::syncGenres((int)$movie['id'], $data['genre_ids']);
            });
        } catch (Throwable $ex) {
            deletePosterFile($poster);
            throw $ex;
        }
        if ($poster !== null) deletePosterFile($movie['poster']);
        setFlash('success', 'Đã cập nhật phim.');
        redirect('admin/movies');
    }

    private static function uploadPoster(array &$errors): ?string
    {
        $file = $_FILES['poster'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        try {
            return handlePosterUpload($file);
        } catch (RuntimeException $ex) {
            $errors['poster'] = $ex->getMessage();
            return null;
        }
    }

    public static function status(): void
    {
        requireRole('admin');
        requireCsrf();
        $movie = Movie::findAny(intOrZero($_POST['id'] ?? 0));
        if (!$movie) { renderError(404, 'Không tìm thấy phim.'); return; }
        if ($movie['status'] === 'hidden') {
            $next = $movie['release_date'] > date('Y-m-d') ? 'upcoming' : 'now_showing';
        } else {
            $next = 'hidden';
        }
        Movie::setStatus((int)$movie['id'], $next);
        setFlash('success', $next === 'hidden' ? 'Đã ẩn phim.' : 'Đã hiện phim.');
        redirect('admin/movies');
    }

    public static function delete(): void
    {
        requireRole('admin');
        requireCsrf();
        $movie = Movie::findAny(intOrZero($_POST['id'] ?? 0));
        if (!$movie) { renderError(404, 'Không tìm thấy phim.'); return; }
        try {
            Movie::delete((int)$movie['id']);
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
            redirect('admin/movies');
        }
        deletePosterFile($movie['poster']);
        setFlash('success', 'Đã xóa phim.');
        redirect('admin/movies');
    }
}
