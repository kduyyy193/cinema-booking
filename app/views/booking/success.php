<div class="container py-4" style="max-width:760px">
    <div class="panel">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h3 class="mb-1">Vé của bạn</h3>
                <div class="text-muted-2">Mã đơn <strong class="text-light"><?= e($booking['booking_code']) ?></strong></div>
            </div>
            <span class="badge fs-6 bg-<?= e(statusBadgeClass($booking['status'])) ?>"><?= e(bookingStatusLabel($booking['status'])) ?></span>
        </div>
        <dl class="row mb-3">
            <dt class="col-sm-3 text-muted-2">Phim</dt><dd class="col-sm-9"><?= e($booking['movie_title']) ?></dd>
            <dt class="col-sm-3 text-muted-2">Rạp</dt><dd class="col-sm-9"><?= e($booking['cinema_name']) ?></dd>
            <dt class="col-sm-3 text-muted-2">Phòng</dt><dd class="col-sm-9"><?= e($booking['room_name']) ?></dd>
            <dt class="col-sm-3 text-muted-2">Giờ chiếu</dt><dd class="col-sm-9"><?= e(formatDateTime($booking['start_time'])) ?></dd>
            <dt class="col-sm-3 text-muted-2">Ghế</dt><dd class="col-sm-9"><?= e(implode(', ', array_map(fn($s) => $s['seat_row'] . $s['seat_number'], $seats))) ?></dd>
            <dt class="col-sm-3 text-muted-2">Tổng tiền</dt><dd class="col-sm-9 fw-bold"><?= e(formatVnd((int)$booking['total_amount'])) ?></dd>
            <?php if ($payment): ?>
                <dt class="col-sm-3 text-muted-2">Thanh toán</dt><dd class="col-sm-9"><span class="badge bg-<?= e(statusBadgeClass($payment['status'])) ?>"><?= e(paymentStatusLabel($payment['status'])) ?></span></dd>
            <?php endif; ?>
        </dl>
        <div class="d-flex gap-2 no-print">
            <button type="button" id="print-ticket" class="btn btn-detail"><i class="bi bi-printer me-1"></i>In vé</button>
            <a class="btn btn-outline-light" href="/my-bookings">Vé của tôi</a>
        </div>
    </div>
</div>
<script>document.getElementById('print-ticket').addEventListener('click', function () { window.print(); });</script>
