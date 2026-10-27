<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Đơn <?= e($booking['booking_code']) ?></h3>
        <a class="btn btn-outline-light btn-sm" href="/admin/bookings">‹ Danh sách</a>
    </div>
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="adm-panel mb-3">
                <div class="d-flex justify-content-between mb-3">
                    <h6 class="mb-0">Thông tin đơn</h6>
                    <span class="badge bg-<?= e(statusBadgeClass($booking['status'])) ?>"><?= e(bookingStatusLabel($booking['status'])) ?></span>
                </div>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4 text-muted-2">Khách</dt><dd class="col-sm-8"><?= e($booking['user_name']) ?> · <?= e($booking['user_email']) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Phim</dt><dd class="col-sm-8"><?= e($booking['movie_title']) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Rạp – Phòng</dt><dd class="col-sm-8"><?= e($booking['cinema_name']) ?> – <?= e($booking['room_name']) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Giờ chiếu</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['start_time'])) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Ngày đặt</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['created_at'])) ?></dd>
                    <dt class="col-sm-4 text-muted-2">Hạn giữ ghế</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['expires_at'])) ?></dd>
                    <?php if ($booking['cancelled_at']): ?><dt class="col-sm-4 text-muted-2">Hủy lúc</dt><dd class="col-sm-8"><?= e(formatDateTime($booking['cancelled_at'])) ?></dd><?php endif; ?>
                </dl>
            </div>
            <div class="adm-panel">
                <h6 class="mb-3">Ghế</h6>
                <table class="table table-sm mb-0">
                    <?php foreach ($seats as $s): ?>
                        <tr><td><?= e($s['seat_row'] . $s['seat_number']) ?></td><td><?= $s['seat_type'] === 'vip' ? 'VIP' : 'Thường' ?></td><td class="text-end"><?= e(formatVnd((int)$s['price'])) ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold"><td colspan="2">Tổng</td><td class="text-end"><?= e(formatVnd((int)$booking['total_amount'])) ?></td></tr>
                </table>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="adm-panel mb-3">
                <h6 class="mb-3">Hành động</h6>
                <?php if (!$actions): ?><p class="text-muted-2 small mb-0">Đơn ở trạng thái này không còn thao tác hợp lệ.</p><?php endif; ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($actions as $status => $label): ?>
                        <form method="post" action="/admin/bookings/status" onsubmit="return confirm('<?= e($label) ?>?')">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$booking['id'] ?>"><input type="hidden" name="status" value="<?= e($status) ?>">
                            <button class="btn w-100 btn-<?= $status === 'cancelled' ? 'outline-danger' : 'primary' ?>"><?= e($label) ?></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="adm-panel">
                <h6 class="mb-3">Lịch sử thanh toán</h6>
                <?php if (!$payments): ?><p class="text-muted-2 small mb-0">Chưa có giao dịch.</p><?php endif; ?>
                <?php foreach ($payments as $p): ?>
                    <div class="border-bottom border-secondary-subtle pb-2 mb-2 small">
                        <div class="d-flex justify-content-between"><strong><?= e($p['transaction_code']) ?></strong>
                            <span class="badge bg-<?= e(statusBadgeClass($p['status'])) ?>"><?= e(paymentStatusLabel($p['status'])) ?></span></div>
                        <div class="text-muted-2"><?= e(formatVnd((int)$p['amount'])) ?> · <?= e($p['method']) ?> · tạo <?= e(formatDateTime($p['created_at'])) ?><?= $p['paid_at'] ? ' · trả ' . e(formatDateTime($p['paid_at'])) : '' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
