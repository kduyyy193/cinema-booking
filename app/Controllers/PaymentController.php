<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Payment;

class PaymentController
{
    private static function ownedBooking(array $user, int $bookingId): ?array
    {
        $booking = Booking::find($bookingId);
        if (!$booking) { renderError(404, 'Không tìm thấy đơn.'); return null; }
        if ((int)$booking['user_id'] !== (int)$user['id']) { renderError(403, 'Bạn không có quyền xem đơn này.'); return null; }
        return $booking;
    }

    public static function show(): void
    {
        $user = requireLogin();
        Booking::expireStale();
        $booking = self::ownedBooking($user, intOrZero($_GET['booking_id'] ?? 0));
        if (!$booking) return;

        if ($booking['status'] === 'confirmed') {
            redirect('payment/success?code=' . urlencode($booking['booking_code']));
        }
        if ($booking['status'] !== 'pending' || $booking['expires_at'] < nowString()) {
            setFlash('warning', 'Đơn này không còn chờ thanh toán.');
            redirect('my-bookings');
        }

        $payment = Payment::forBooking((int)$booking['id']);
        $failed = $payment !== null && $payment['status'] === 'failed';
        if (!$payment || (!$failed && !in_array($payment['status'], ['pending', 'processing'], true))) {
            $payment = Payment::getOrCreatePending((int)$booking['id']);
        }
        $form = $_SESSION['payment_form'] ?? [];
        unset($_SESSION['payment_form']);

        render('payment/index', [
            'pageTitle' => 'Thanh toán', 'booking' => $booking, 'seats' => Booking::seats((int)$booking['id']),
            'payment' => $payment, 'failed' => $failed, 'qr' => generatePaymentQr($payment['transaction_code']),
            'form' => $form, 'watchMode' => paymentWatchMode(), 'scripts' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><script src="' . assetUrl('js/payment.js') . '"></script>',
        ], 'payment');
    }

    public static function create(): void
    {
        $user = requireLogin();
        requireCsrf();
        Booking::expireStale();
        $booking = self::ownedBooking($user, intOrZero($_POST['booking_id'] ?? 0));
        if (!$booking) return;
        if ($booking['status'] !== 'pending' || $booking['expires_at'] < nowString()) {
            setFlash('warning', 'Đơn này không còn chờ thanh toán.');
            redirect('my-bookings');
        }
        Payment::getOrCreatePending((int)$booking['id']);
        redirect('payment?booking_id=' . (int)$booking['id']);
    }

    public static function processDemo(): void
    {
        $user = requireLogin();
        requireCsrf();

        $card = [
            'card_name' => (string)($_POST['card_name'] ?? ''), 'card_number' => (string)($_POST['card_number'] ?? ''),
            'card_expiry' => (string)($_POST['card_expiry'] ?? ''), 'card_cvv' => (string)($_POST['card_cvv'] ?? ''),
        ];
        unset($_POST['card_number'], $_POST['card_cvv'], $_POST['card_expiry'], $_POST['card_name']);

        $payment = Payment::findByTransactionCode(trim((string)($_POST['transaction_code'] ?? '')));
        if (!$payment) { renderError(404, 'Không tìm thấy giao dịch.'); return; }
        $booking = Booking::find((int)$payment['booking_id']);
        if (!$booking || (int)$booking['user_id'] !== (int)$user['id']) { renderError(403, 'Bạn không có quyền với giao dịch này.'); return; }
        $back = 'payment?booking_id=' . (int)$booking['id'];

        if ($payment['status'] !== 'pending' || $booking['status'] !== 'pending'
            || $booking['expires_at'] < nowString() || !Payment::verifyPaymentAmount($payment, $booking)) {
            setFlash('danger', 'Giao dịch không hợp lệ hoặc đã được xử lý.');
            redirect($back);
        }

        $errors = validateDemoCard($card);
        if ($errors) {
            $_SESSION['payment_form'] = ['errors' => $errors, 'card_name' => trim($card['card_name'])];
            redirect($back);
        }

        $result = simulateGatewayResult($card['card_number']);
        unset($card);

        if (!Payment::markProcessing((int)$payment['id'])) {
            setFlash('info', 'Giao dịch đang được xử lý.');
            redirect($back);
        }
        if (!sendMockWebhook($result === 'success' ? 'payment.succeeded' : 'payment.failed', $payment)) {
            logApplicationError('Không gửi được webhook cho giao dịch ' . $payment['transaction_code']);
        }
        redirect($back);
    }

    public static function simulateQr(): void
    {
        $user = requireLogin();
        requireCsrf();

        $payment = Payment::findByTransactionCode(trim((string)($_POST['transaction_code'] ?? '')));
        if (!$payment) { renderError(404, 'Không tìm thấy giao dịch.'); return; }
        $booking = Booking::find((int)$payment['booking_id']);
        if (!$booking || (int)$booking['user_id'] !== (int)$user['id']) { renderError(403, 'Bạn không có quyền với giao dịch này.'); return; }
        $back = 'payment?booking_id=' . (int)$booking['id'];

        if ($payment['status'] !== 'pending' || $booking['status'] !== 'pending'
            || $booking['expires_at'] < nowString() || !Payment::verifyPaymentAmount($payment, $booking)) {
            setFlash('danger', 'Giao dịch không hợp lệ hoặc đã được xử lý.');
            redirect($back);
        }
        if (!Payment::markProcessing((int)$payment['id'])) {
            setFlash('info', 'Giao dịch đang được xử lý.');
            redirect($back);
        }
        if (!sendMockWebhook('payment.succeeded', $payment)) {
            logApplicationError('Không gửi được webhook cho giao dịch ' . $payment['transaction_code']);
        }
        redirect($back);
    }

    public static function status(): void
    {
        $user = requireLogin();
        Booking::expireStale();
        $payment = Payment::findByTransactionCode(trim((string)($_GET['transaction_code'] ?? '')));
        if (!$payment) jsonResponse(['error' => 'not_found'], 404);
        $booking = Booking::find((int)$payment['booking_id']);
        if (!$booking || (int)$booking['user_id'] !== (int)$user['id']) jsonResponse(['error' => 'forbidden'], 403);
        jsonResponse([
            'status' => Payment::status((int)$payment['id']),
            'booking_status' => $booking['status'],
            'booking_code' => $booking['booking_code'],
        ]);
    }

    public static function stream(): void
    {
        $user = requireLogin();
        Booking::expireStale();
        $payment = Payment::findByTransactionCode(trim((string)($_GET['transaction_code'] ?? '')));
        if (!$payment) { renderError(404, 'Không tìm thấy giao dịch.'); return; }
        $booking = Booking::find((int)$payment['booking_id']);
        if (!$booking || (int)$booking['user_id'] !== (int)$user['id']) { renderError(403, 'Bạn không có quyền với giao dịch này.'); return; }

        session_write_close();
        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        @ini_set('zlib.output_compression', '0');
        while (ob_get_level() > 0) ob_end_flush();
        set_time_limit(40);

        echo "retry: 2000\n\n";
        $last = '';
        $deadline = time() + 25;
        $tick = 0;
        while (time() < $deadline && !connection_aborted()) {
            if ($tick > 0 && $tick % 5 === 0) Booking::expireStale();
            $status = Payment::status((int)$payment['id']);
            $current = Booking::find((int)$booking['id']);
            $data = json_encode([
                'status' => $status,
                'booking_status' => $current['status'] ?? $booking['status'],
                'booking_code' => $booking['booking_code'],
            ], JSON_UNESCAPED_UNICODE);
            if ($data !== $last) {
                echo "event: status\ndata: $data\n\n";
                $last = $data;
                if (in_array($status, ['success', 'failed', 'expired', 'refunded'], true)) {
                    echo "event: done\ndata: {}\n\n";
                    flush();
                    return;
                }
            } elseif ($tick % 10 === 9) {
                echo ": ping\n\n";
            }
            flush();
            sleep(1);
            $tick++;
        }
    }

    public static function success(): void
    {
        $user = requireLogin();
        $booking = Booking::findByCode(trim((string)($_GET['code'] ?? '')));
        if (!$booking) { renderError(404, 'Không tìm thấy đơn.'); return; }
        if ((int)$booking['user_id'] !== (int)$user['id']) { renderError(403, 'Bạn không có quyền xem đơn này.'); return; }
        if ($booking['status'] !== 'confirmed') redirect('payment?booking_id=' . (int)$booking['id']);

        render('payment/success', [
            'pageTitle' => 'Thanh toán thành công', 'booking' => $booking,
            'seats' => Booking::seats((int)$booking['id']), 'payment' => Payment::forBooking((int)$booking['id']),
        ]);
    }
}
