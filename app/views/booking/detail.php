<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Chi tiết vé</h3>
        <a class="btn btn-outline-light btn-sm" href="/my-bookings">‹ Vé của tôi</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <div class="d-flex justify-content-between mb-3">
                    <div>Mã đơn <strong><?= e($booking['booking_code']) ?></strong></div>
                    <span class="badge fs-6 bg-<?= e(statusBadgeClass($booking['status'])) ?>"><?= e(bookingStatusLabel($booking['status'])) ?></span>
                </div>
                <dl class="row mb-3">
                    <dt class="col-sm-4 text-muted-2">Phim</dt><dd class="col-sm-8"><?= e($booking['movie_title']) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Rạp – Phòng</dt><dd class="col-sm-8"><?= e($booking['cinema_name']) ?> – <?= e($booking['room_name']) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Giờ chiếu</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['start_time'])) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Ngày đặt</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['created_at'])) ?></dd>
                    <?php if ($booking['status'] === 'pending'): ?>
                        <dt class="col-sm-4 text-muted-2">Giữ ghế đến</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['expires_at'])) ?></dd>
                    <?php endif; ?>
                    <?php if ($booking['cancelled_at']): ?>
                        <dt class="col-sm-4 text-muted-2">Đã hủy lúc</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['cancelled_at'])) ?></dd>
                    <?php endif; ?>
                    <?php if ($payment): ?>
                        <dt class="col-sm-4 text-muted-2">Thanh toán</dt><dd class="col-sm-8"><span class="badge bg-<?= e(statusBadgeClass($payment['status'])) ?>"><?= e(paymentStatusLabel($payment['status'])) ?></span> <small class="text-muted-2"><?= e($payment['transaction_code']) ?></small></dd>
                    <?php endif; ?>
                </dl>
                <table class="table table-dark align-middle">
                    <thead><tr><th>Ghế</th><th>Loại</th><th class="text-end">Giá</th></tr></thead>
                    <?php foreach ($seats as $s): ?>
                        <tr><td><?= e($s['seat_row'] . $s['seat_number']) ?></td><td><?= $s['seat_type'] === 'vip' ? 'VIP' : 'Thường' ?></td><td class="text-end"><?= e(formatVnd((int)$s['price'])) ?></td></tr>
                    <?php endforeach; ?>
                    <tfoot><tr><th colspan="2">Tổng</th><th class="text-end"><?= e(formatVnd((int)$booking['total_amount'])) ?></th></tr></tfoot>
                </table>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($canPay): ?><a class="btn btn-buy" href="/payment?booking_id=<?= (int)$booking['id'] ?>">Thanh toán</a><?php endif; ?>
                    <?php if ($canCancel): ?>
                        <form method="post" action="/booking/cancel" data-confirm="Bạn chắc chắn muốn hủy vé này? Số tiền được hoàn lại (giả lập)." data-confirm-button="Hủy vé">
                            <?= csrfField() ?><input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                            <button class="btn btn-outline-danger">Hủy vé</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($booking['status'] === 'confirmed'): ?><a class="btn btn-detail" href="/booking-success?code=<?= e(urlencode($booking['booking_code'])) ?>">Xem và in vé</a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if ($booking['status'] === 'confirmed'): ?>
        <div class="col-lg-5">
            <div class="ticket">
                <div class="fw-bold fs-5"><?= e($booking['movie_title']) ?></div>
                <div class="qr-box"><div id="ticket-qr" data-code="<?= e($booking['booking_code']) ?>"></div></div>
                <dl>
                    <div><dt>Ngày</dt><dd><?= e(formatDateTime($booking['start_time'], 'd/m/Y')) ?></dd></div>
                    <div><dt>Giờ</dt><dd><?= e(formatDateTime($booking['start_time'], 'H:i')) ?></dd></div>
                    <div><dt>Giá</dt><dd><?= e(formatVnd((int)$booking['total_amount'])) ?></dd></div>
                    <div><dt>Rạp</dt><dd><?= e($booking['cinema_name']) ?></dd></div>
                    <div><dt>Phòng</dt><dd><?= e($booking['room_name']) ?></dd></div>
                    <div><dt>Ghế</dt><dd><?= e(implode(', ', array_map(fn($s) => $s['seat_row'] . $s['seat_number'], $seats))) ?></dd></div>
                </dl>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
