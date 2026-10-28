<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use InvalidArgumentException;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;

class AdminSeatController
{
    public static function index(): void
    {
        requireRole('admin');
        $cinemas = Cinema::allIncludingInactive();
        $roomId = intOrZero($_GET['room_id'] ?? 0);
        $room = $roomId > 0 ? Room::find($roomId) : null;
        if (!$room && $cinemas) {
            $first = Room::byCinema((int)$cinemas[0]['id']);
            $room = $first ? Room::find((int)$first[0]['id']) : null;
        }
        $rows = [];
        $rooms = [];
        if ($room) {
            foreach (Seat::byRoom((int)$room['id']) as $seat) $rows[$seat['seat_row']][] = $seat;
            $rooms = Room::byCinema((int)$room['cinema_id']);
        }
        render('admin/seats/index', [
            'pageTitle' => 'Ghế', 'cinemas' => $cinemas, 'room' => $room, 'rooms' => $rooms, 'rows' => $rows,
        ], 'admin');
    }

    private static function targetSeat(): array
    {
        $seat = Seat::find(intOrZero($_POST['seat_id'] ?? 0));
        if (!$seat) { renderError(404, 'Không tìm thấy ghế.'); exit; }
        return $seat;
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $seat = self::targetSeat();
        try {
            Seat::updateType((int)$seat['id'], (string)($_POST['type'] ?? ''));
            setFlash('success', 'Đã đổi loại ghế ' . $seat['seat_row'] . $seat['seat_number'] . '.');
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
        }
        redirect('admin/seats?room_id=' . (int)$seat['room_id']);
    }

    public static function lock(): void
    {
        requireRole('admin');
        requireCsrf();
        $seat = self::targetSeat();
        $locked = ($_POST['locked'] ?? '0') === '1';
        try {
            Seat::setLocked((int)$seat['id'], $locked);
            setFlash('success', ($locked ? 'Đã khóa ghế ' : 'Đã mở ghế ') . $seat['seat_row'] . $seat['seat_number'] . '.');
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
        }
        redirect('admin/seats?room_id=' . (int)$seat['room_id']);
    }
}
