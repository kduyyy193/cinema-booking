<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use InvalidArgumentException;
use App\Models\Cinema;
use App\Models\Room;

class AdminRoomController
{
    public static function index(): void
    {
        requireRole('admin');
        $cinemas = Cinema::allIncludingInactive();
        $cinemaId = intOrZero($_GET['cinema_id'] ?? 0);
        $cinema = $cinemaId > 0 ? Cinema::find($cinemaId) : ($cinemas[0] ?? null);
        $rooms = [];
        if ($cinema) {
            foreach (Room::byCinema((int)$cinema['id']) as $room) {
                $room['summary'] = Room::seatSummary((int)$room['id']);
                $room['has_bookings'] = Room::hasBookings((int)$room['id']);
                $rooms[] = $room;
            }
        }
        render('admin/rooms/index', ['pageTitle' => 'Phòng chiếu', 'cinemas' => $cinemas, 'cinema' => $cinema, 'rooms' => $rooms], 'admin');
    }

    public static function store(): void
    {
        requireRole('admin');
        requireCsrf();
        $cinemaId = intOrZero($_POST['cinema_id'] ?? 0);
        if (!Cinema::find($cinemaId)) { renderError(404, 'Không tìm thấy rạp.'); return; }
        [$errors, $data] = Room::validate($_POST, true);
        if ($errors) {
            setFlash('danger', implode(' ', $errors));
        } else {
            Room::create($data + ['cinema_id' => $cinemaId]);
            setFlash('success', 'Đã tạo phòng và sinh ghế.');
        }
        redirect('admin/rooms?cinema_id=' . $cinemaId);
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $room = Room::find(intOrZero($_POST['id'] ?? 0));
        if (!$room) { renderError(404, 'Không tìm thấy phòng.'); return; }
        [$errors, $data] = Room::validate($_POST, false);
        if ($errors) {
            setFlash('danger', implode(' ', $errors));
        } else {
            try {
                Room::update((int)$room['id'], $data);
                setFlash('success', 'Đã cập nhật phòng.');
            } catch (InvalidArgumentException $ex) {
                setFlash('danger', $ex->getMessage());
            }
        }
        redirect('admin/rooms?cinema_id=' . (int)$room['cinema_id']);
    }
}
