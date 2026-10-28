<?php
declare(strict_types=1);

namespace App\Controllers;

use InvalidArgumentException;
use App\Models\Booking;
use App\Models\SeatTakenException;
use App\Models\Setting;
use App\Models\Showtime;

class BookingController
{
    public static function seats(): void
    {
        $showtimeId = intOrZero($_GET['showtime'] ?? 0);
        Booking::expireStale();
        if (!Showtime::isBookable($showtimeId)) {
            renderError(404, 'Suất chiếu không tồn tại hoặc không còn khả dụng.');
            return;
        }
        $rows = [];
        foreach (Booking::seatMap($showtimeId) as $seat) {
            $rows[$seat['seat_row']][] = $seat;
        }
        render('booking/select-seats', [
            'pageTitle' => 'Chọn ghế', 'showtime' => Showtime::find($showtimeId), 'rows' => $rows,
            'maxSeats' => Booking::MAX_SEATS,
            'scripts' => '<script src="' . assetUrl('js/seats.js') . '"></script>',
        ], 'seat');
    }

    public static function checkout(): void
    {
        requireLogin();
        $showtimeId = intOrZero($_GET['showtime'] ?? 0);
        $seatIds = postedIds($_GET['seats'] ?? []);
        Booking::expireStale();
        try {
            $selection = Booking::validateSelection($showtimeId, $seatIds);
        } catch (SeatTakenException $ex) {
            setFlash('danger', $ex->getMessage() . ' Vui lòng chọn ghế khác.');
            redirect('select-seats?showtime=' . $showtimeId);
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
            redirect($showtimeId > 0 ? 'select-seats?showtime=' . $showtimeId : 'showtimes');
        }
        render('booking/checkout', [
            'pageTitle' => 'Xác nhận đơn', 'showtime' => $selection['showtime'], 'seats' => $selection['seats'],
            'total' => $selection['total'], 'holdMinutes' => Setting::int('hold_minutes', 10),
        ]);
    }

    public static function store(): void
    {
        $user = requireLogin();
        requireCsrf();
        $showtimeId = intOrZero($_POST['showtime'] ?? 0);
        $seatIds = postedIds($_POST['seats'] ?? []);
        Booking::expireStale();
        try {
            $booking = Booking::create((int)$user['id'], $showtimeId, $seatIds);
        } catch (SeatTakenException $ex) {
            setFlash('danger', 'Rất tiếc, ghế bạn chọn vừa được người khác đặt. Vui lòng chọn lại.');
            redirect('select-seats?showtime=' . $showtimeId);
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
            redirect($showtimeId > 0 ? 'select-seats?showtime=' . $showtimeId : 'showtimes');
        }
        redirect('payment?booking_id=' . (int)$booking['id']);
    }

    public static function cancel(): void
    {
        $user = requireLogin();
        requireCsrf();
        $booking = Booking::find(intOrZero($_POST['booking_id'] ?? 0));
        if (!$booking) { renderError(404, 'Không tìm thấy đơn.'); return; }
        if ((int)$booking['user_id'] !== (int)$user['id']) { renderError(403, 'Bạn không có quyền hủy đơn này.'); return; }

        $reason = Booking::cancelBlockReason($booking);
        if ($reason !== null) {
            setFlash('danger', $reason);
            redirect('booking-detail?id=' . (int)$booking['id']);
        }
        $paid = $booking['status'] === 'confirmed';
        try {
            Booking::cancel((int)$booking['id']);
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
            redirect('my-bookings');
        }
        setFlash('success', $paid ? 'Đã hủy vé. Số tiền được hoàn lại (giả lập).' : 'Đã hủy vé.');
        redirect('my-bookings');
    }
}
