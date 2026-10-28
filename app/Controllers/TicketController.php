<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Payment;

class TicketController
{
    private static function owned(array $user, ?array $booking): ?array
    {
        if (!$booking || (int)$booking['user_id'] !== (int)$user['id']) {
            renderError(404, 'Không tìm thấy vé.');
            return null;
        }
        return $booking;
    }

    public static function success(): void
    {
        $user = requireLogin();
        Booking::expireStale();
        $booking = self::owned($user, Booking::findByCode(trim((string)($_GET['code'] ?? ''))));
        if (!$booking) return;
        render('booking/success', [
            'pageTitle' => 'Kết quả đặt vé', 'booking' => $booking,
            'seats' => Booking::seats((int)$booking['id']), 'payment' => Payment::forBooking((int)$booking['id']),
        ]);
    }

    public static function mine(): void
    {
        $user = requireLogin();
        Booking::expireStale();
        $filters = [
            'status' => (string)($_GET['status'] ?? ''), 'date_from' => (string)($_GET['date_from'] ?? ''),
            'date_to' => (string)($_GET['date_to'] ?? ''), 'q' => (string)($_GET['q'] ?? ''),
        ];
        [$rows, $pg] = Booking::forUser((int)$user['id'], $filters, max(1, intOrZero($_GET['page'] ?? 1)));
        render('booking/my-bookings', ['pageTitle' => 'Vé của tôi', 'rows' => $rows, 'pg' => $pg, 'filters' => $filters]);
    }

    public static function detail(): void
    {
        $user = requireLogin();
        Booking::expireStale();
        $booking = self::owned($user, Booking::find(intOrZero($_GET['id'] ?? 0)));
        if (!$booking) return;
        render('booking/detail', [
            'pageTitle' => 'Chi tiết vé', 'booking' => $booking, 'seats' => Booking::seats((int)$booking['id']),
            'payment' => Payment::forBooking((int)$booking['id']),
            'canPay' => $booking['status'] === 'pending' && $booking['expires_at'] >= nowString(),
            'canCancel' => Booking::canCancel($booking),
            'scripts' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><script src="' . assetUrl('js/ticket.js') . '"></script>',
        ]);
    }
}
