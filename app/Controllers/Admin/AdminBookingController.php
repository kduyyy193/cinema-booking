<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use InvalidArgumentException;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\Payment;

class AdminBookingController
{
    public static function index(): void
    {
        requireRole('admin');
        Booking::expireStale();
        $filters = [
            'q' => (string)($_GET['q'] ?? ''), 'date_from' => (string)($_GET['date_from'] ?? ''), 'date_to' => (string)($_GET['date_to'] ?? ''),
            'movie_id' => intOrZero($_GET['movie_id'] ?? 0), 'status' => (string)($_GET['status'] ?? ''),
        ];
        [$rows, $pg] = Booking::adminList($filters, max(1, intOrZero($_GET['page'] ?? 1)));
        render('admin/bookings/index', [
            'pageTitle' => 'Đơn vé', 'rows' => $rows, 'pg' => $pg, 'filters' => $filters, 'movies' => Movie::allTitles(),
        ], 'admin');
    }

    public static function show(): void
    {
        requireRole('admin');
        Booking::expireStale();
        $booking = Booking::find(intOrZero($_GET['id'] ?? 0));
        if (!$booking) { renderError(404, 'Không tìm thấy đơn.'); return; }
        render('admin/bookings/show', [
            'pageTitle' => 'Đơn ' . $booking['booking_code'], 'booking' => $booking, 'seats' => Booking::seats((int)$booking['id']),
            'payments' => Payment::allForBooking((int)$booking['id']), 'actions' => Booking::availableAdminActions($booking),
        ], 'admin');
    }

    public static function status(): void
    {
        requireRole('admin');
        requireCsrf();
        $id = intOrZero($_POST['id'] ?? 0);
        if (!Booking::find($id)) { renderError(404, 'Không tìm thấy đơn.'); return; }
        try {
            Booking::updateStatus($id, (string)($_POST['status'] ?? ''));
            setFlash('success', 'Đã cập nhật trạng thái đơn.');
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
        }
        redirect('admin/bookings/show?id=' . $id);
    }
}
