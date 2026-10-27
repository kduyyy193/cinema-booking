<?php
$errors = $form['errors'] ?? [];
$processing = $payment['status'] === 'processing';
$seatLabels = implode(', ', array_map(fn($s) => $s['seat_row'] . $s['seat_number'], $seats));
?>
<div class="container-xl py-4" id="pay-root" data-transaction="<?= e($payment['transaction_code']) ?>" data-status="<?= e($payment['status']) ?>" data-expires="<?= (int)strtotime($booking['expires_at']) ?>">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="pay-card">
                <h6 class="text-warning mb-3">Tóm tắt thanh toán</h6>
                <dl class="small mb-3">
                    <dt class="text-muted-2">Mã đơn</dt><dd><?= e($booking['booking_code']) ?></dd>
                    <dt class="text-muted-2">Mã giao dịch</dt><dd><?= e($payment['transaction_code']) ?></dd>
                    <dt class="text-muted-2">Phim</dt><dd><?= e($booking['movie_title']) ?></dd>
                    <dt class="text-muted-2">Rạp – Phòng</dt><dd><?= e($booking['cinema_name']) ?> – <?= e($booking['room_name']) ?></dd>
                    <dt class="text-muted-2">Giờ chiếu</dt><dd><?= e(formatDateTime($booking['start_time'])) ?></dd>
                    <dt class="text-muted-2">Ghế</dt><dd><?= e($seatLabels) ?></dd>
                </dl>
                <table class="table table-dark table-sm small">
                    <?php foreach ($seats as $s): ?>
                        <tr><td><?= e($s['seat_row'] . $s['seat_number']) ?></td><td class="text-end"><?= e(formatVnd((int)$s['price'])) ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold"><td>Tổng thanh toán</td><td class="text-end text-warning"><?= e(formatVnd((int)$payment['amount'])) ?></td></tr>
                </table>
                <div class="text-center">
                    <div class="small text-muted-2">Còn lại để thanh toán</div>
                    <div class="fs-3 fw-bold text-warning" id="countdown">--:--</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="pay-card">
                <h6 class="text-warning mb-3">Phương thức thanh toán</h6>
                <?php if ($failed): ?>
                    <div class="alert alert-danger">Thanh toán thất bại (thẻ bị từ chối). Bạn có thể thử lại trước khi hết hạn.</div>
                    <form method="post" action="/payment/create">
                        <?= csrfField() ?><input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                        <button class="btn-pay">Thử lại</button>
                    </form>
                <?php elseif ($processing): ?>
                    <div class="alert alert-info d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm"></span>Đang xử lý giao dịch, vui lòng chờ...</div>
                <?php else: ?>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item"><button class="nav-link <?= $errors ? "" : "active" ?>" data-bs-toggle="tab" data-bs-target="#tab-qr" type="button">Quét QR</button></li>
                        <li class="nav-item"><button class="nav-link <?= $errors ? "active" : "" ?>" data-bs-toggle="tab" data-bs-target="#tab-card" type="button">Thẻ</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade <?= $errors ? "" : "show active" ?> text-center" id="tab-qr">
                            <div class="bg-white d-inline-block p-3 rounded"><div id="qr-box" data-qr="<?= e($qr) ?>"></div></div>
                            <p class="small text-muted-2 mt-2">Mã QR demo, không quét được bằng ứng dụng ngân hàng thật. Bấm nút dưới đây để giả lập khách đã quét và thanh toán thành công.</p>
                            <form method="post" action="/payment/simulate-qr">
                                <?= csrfField() ?>
                                <input type="hidden" name="transaction_code" value="<?= e($payment['transaction_code']) ?>">
                                <button type="submit" class="btn-pay">Tôi đã quét và thanh toán (demo)</button>
                            </form>
                        </div>
                        <div class="tab-pane fade <?= $errors ? "show active" : "" ?>" id="tab-card">
                            <form method="post" action="/payment/process-demo" id="card-form" autocomplete="off" novalidate>
                                <?= csrfField() ?>
                                <input type="hidden" name="transaction_code" value="<?= e($payment['transaction_code']) ?>">
                                <?php if ($errors): ?><div class="alert alert-danger small"><?php foreach ($errors as $msg): ?><div><?= e($msg) ?></div><?php endforeach; ?></div><?php endif; ?>
                                <div class="mb-2"><label class="form-label">Tên chủ thẻ</label><input class="form-control" name="card_name" maxlength="100" autocomplete="off" value="<?= e($form['card_name'] ?? '') ?>" required></div>
                                <div class="mb-2"><label class="form-label">Số thẻ</label><input class="form-control" name="card_number" inputmode="numeric" maxlength="23" placeholder="4242 4242 4242 4242" autocomplete="off" required></div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><label class="form-label">Hạn (MM/YY)</label><input class="form-control" name="card_expiry" placeholder="12/30" maxlength="5" autocomplete="off" required></div>
                                    <div class="col-6"><label class="form-label">CVV</label><input class="form-control" name="card_cvv" inputmode="numeric" maxlength="3" autocomplete="off" required></div>
                                </div>
                                <button type="submit" class="btn-pay">Thanh toán <?= e(formatVnd((int)$payment['amount'])) ?></button>
                            </form>
                            <div class="small text-muted-2 mt-3">
                                Thẻ test: <code>4242 4242 4242 4242</code> thành công, <code>4000 0000 0000 0002</code> bị từ chối. Hạn bất kỳ trong tương lai, CVV 3 số.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="pay-card">
                <h6 class="text-warning mb-3">Vé của bạn</h6>
                <div class="ticket">
                    <div class="fw-bold fs-5"><?= e($booking['movie_title']) ?></div>
                    <dl class="mt-3">
                        <div><dt>Ngày</dt><dd><?= e(formatDateTime($booking['start_time'], 'd/m/Y')) ?></dd></div>
                        <div><dt>Giờ</dt><dd><?= e(formatDateTime($booking['start_time'], 'H:i')) ?></dd></div>
                        <div><dt>Giá</dt><dd><?= e(formatVnd((int)$booking['total_amount'])) ?></dd></div>
                        <div><dt>Rạp</dt><dd><?= e($booking['cinema_name']) ?></dd></div>
                        <div><dt>Phòng</dt><dd><?= e($booking['room_name']) ?></dd></div>
                        <div><dt>Ghế</dt><dd><?= e($seatLabels) ?></dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
