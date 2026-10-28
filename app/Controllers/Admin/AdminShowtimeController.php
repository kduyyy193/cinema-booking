<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;

class AdminShowtimeController
{
    private static function form(array $showtime, array $errors, string $title, bool $priceOnly = false): void
    {
        render('admin/showtimes/form', [
            'pageTitle' => $title, 'showtime' => $showtime, 'errors' => $errors, 'priceOnly' => $priceOnly,
            'movies' => Movie::allTitles(), 'roomGroups' => Room::activeGroupedByCinema(),
        ], 'admin');
    }

    public static function index(): void
    {
        requireRole('admin');
        $filters = [
            'date' => (string)($_GET['date'] ?? ''), 'movie_id' => intOrZero($_GET['movie_id'] ?? 0),
            'cinema_id' => intOrZero($_GET['cinema_id'] ?? 0), 'status' => (string)($_GET['status'] ?? ''),
        ];
        [$rows, $pg] = Showtime::adminList($filters, max(1, intOrZero($_GET['page'] ?? 1)));
        render('admin/showtimes/index', [
            'pageTitle' => 'Suất chiếu', 'rows' => $rows, 'pg' => $pg, 'filters' => $filters,
            'movies' => Movie::allTitles(), 'cinemas' => Cinema::allIncludingInactive(),
        ], 'admin');
    }

    public static function create(): void
    {
        requireRole('admin');
        self::form(['id' => 0, 'movie_id' => '', 'room_id' => '', 'start_time' => '', 'base_price' => 90000, 'vip_surcharge' => 20000], [], 'Thêm suất chiếu');
    }

    public static function store(): void
    {
        requireRole('admin');
        requireCsrf();
        [$errors, $data] = Showtime::validate($_POST);
        if ($errors) {
            self::form($_POST + ['id' => 0], $errors, 'Thêm suất chiếu');
            return;
        }
        Showtime::create($data);
        setFlash('success', 'Đã thêm suất chiếu.');
        redirect('admin/showtimes');
    }

    public static function edit(): void
    {
        requireRole('admin');
        $showtime = Showtime::find(intOrZero($_GET['id'] ?? 0));
        if (!$showtime) { renderError(404, 'Không tìm thấy suất chiếu.'); return; }
        $showtime['start_time'] = date('Y-m-d\TH:i', strtotime($showtime['start_time']));
        self::form($showtime, [], 'Sửa suất chiếu', Showtime::hasActiveSeats((int)$showtime['id']));
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $id = intOrZero($_POST['id'] ?? 0);
        $existing = Showtime::find($id);
        if (!$existing) { renderError(404, 'Không tìm thấy suất chiếu.'); return; }
        [$errors, $data] = Showtime::validate($_POST, $id);
        if ($errors) {
            self::form($_POST + ['id' => $id], $errors, 'Sửa suất chiếu', Showtime::hasActiveSeats($id));
            return;
        }
        Showtime::update($id, $data);
        setFlash('success', 'Đã cập nhật suất chiếu.');
        redirect('admin/showtimes');
    }

    public static function cancel(): void
    {
        requireRole('admin');
        requireCsrf();
        $id = intOrZero($_POST['id'] ?? 0);
        $showtime = Showtime::find($id);
        if (!$showtime) { renderError(404, 'Không tìm thấy suất chiếu.'); return; }
        if ($showtime['status'] === 'cancelled') {
            setFlash('warning', 'Suất chiếu đã được hủy trước đó.');
            redirect('admin/showtimes');
        }
        Showtime::cancel($id);
        setFlash('success', 'Đã hủy suất chiếu; các đơn liên quan được hủy và hoàn tiền giả lập.');
        redirect('admin/showtimes');
    }
}
