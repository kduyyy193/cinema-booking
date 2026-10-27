<div class="seat-layout" id="seat-app" data-showtime="<?= (int)$showtime['id'] ?>" data-max="<?= (int)$maxSeats ?>">
    <div class="seat-map-pane">
        <h3 class="text-center mb-3">Chọn ghế</h3>
        <div class="screen-curve">MÀN HÌNH</div>
        <div class="seat-grid">
            <?php foreach ($rows as $rowLabel => $seats): ?>
                <div class="seat-row">
                    <span class="row-label"><?= e($rowLabel) ?></span>
                    <?php foreach ($seats as $seat):
                        $label = $seat['seat_row'] . $seat['seat_number'];
                        $classes = 'seat' . ($seat['seat_type'] === 'vip' ? ' vip' : '') . ($seat['state'] !== 'free' ? ' ' . $seat['state'] : ''); ?>
                        <button type="button" class="<?= e($classes) ?>" data-seat-id="<?= (int)$seat['id'] ?>" data-price="<?= (int)$seat['price'] ?>" data-label="<?= e($label) ?>"
                            title="<?= e($label . ' · ' . formatVnd((int)$seat['price'])) ?>" <?= $seat['state'] !== 'free' ? 'disabled' : '' ?>><?= (int)$seat['seat_number'] ?></button>
                    <?php endforeach; ?>
                    <span class="row-label"><?= e($rowLabel) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="seat-legend">
            <span><span class="seat"></span>Trống</span>
            <span><span class="seat selected"></span>Đã chọn</span>
            <span><span class="seat taken"></span>Đã đặt</span>
            <span><span class="seat vip"></span>VIP</span>
        </div>
    </div>
    <aside class="seat-summary-pane text-light">
        <div class="d-flex gap-3 mb-3">
            <div style="width:70px;aspect-ratio:2/3;border-radius:6px;background:linear-gradient(160deg,#3a2a6a,#14142b) center/cover;<?= $showtime['poster'] ? "background-image:url('/uploads/" . e($showtime['poster']) . "')" : '' ?>"></div>
            <div>
                <h5 class="mb-1"><?= e($showtime['movie_title']) ?></h5>
                <div class="small text-muted-2"><?= e($showtime['cinema_name']) ?> – <?= e($showtime['room_name']) ?></div>
                <div class="small text-muted-2"><?= e(formatDateTime($showtime['start_time'], 'd/m/Y H:i')) ?></div>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="small text-muted-2 mb-1">Ghế đã chọn (tối đa <?= (int)$maxSeats ?>)</div>
        <div id="selected-list" class="mb-3">Chưa chọn ghế</div>
        <div class="d-flex justify-content-between fs-5 mb-1"><span>Tổng tiền</span><strong id="total">0đ</strong></div>
        <div class="small text-muted-2 mb-3">Giá cuối cùng được tính lại khi xác nhận đơn.</div>
        <div id="seat-msg" class="small text-warning mb-2" role="alert"></div>
        <button type="button" id="continue" class="btn-seat w-100" disabled>Tiếp tục</button>
    </aside>
</div>
