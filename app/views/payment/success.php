<div class="container py-5" style="max-width:640px">
    <div class="pay-card text-center">
        <div class="display-3 text-success"><i class="bi bi-check-circle-fill"></i></div>
        <h3 class="mt-2">Thanh toán thành công</h3>
        <p class="text-muted-2">Cảm ơn bạn đã đặt vé. Chúc bạn xem phim vui vẻ!</p>
        <dl class="row text-start small mt-4">
            <dt class="col-5 text-muted-2">Mã đơn</dt><dd class="col-7"><?= e($booking['booking_code']) ?></dd>
            <dt class="col-5 text-muted-2">Phim</dt><dd class="col-7"><?= e($booking['movie_title']) ?></dd>
            <dt class="col-5 text-muted-2">Rạp – Phòng</dt><dd class="col-7"><?= e($booking['cinema_name']) ?> – <?= e($booking['room_name']) ?></dd>
            <dt class="col-5 text-muted-2">Giờ chiếu</dt><dd class="col-7"><?= e(formatDateTime($booking['start_time'])) ?></dd>
            <dt class="col-5 text-muted-2">Ghế</dt><dd class="col-7"><?= e(implode(', ', array_map(fn($s) => $s['seat_row'] . $s['seat_number'], $seats))) ?></dd>
            <dt class="col-5 text-muted-2">Tổng tiền</dt><dd class="col-7 fw-bold"><?= e(formatVnd((int)$booking['total_amount'])) ?></dd>
            <?php if ($payment): ?><dt class="col-5 text-muted-2">Mã giao dịch</dt><dd class="col-7"><?= e($payment['transaction_code']) ?></dd><?php endif; ?>
        </dl>
        <div class="d-flex gap-2 justify-content-center">
            <a class="btn-pay w-auto px-4 text-decoration-none" href="/booking-success?code=<?= e(urlencode($booking['booking_code'])) ?>">Xem vé</a>
            <a class="btn btn-outline-light" href="/my-bookings">Vé của tôi</a>
        </div>
    </div>
</div>
