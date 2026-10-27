<?php $hasFilter = array_filter($filters, fn($v) => $v !== ''); ?>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h3 class="mb-0">Vé của tôi</h3>
            <div class="small text-muted-2"><?= (int)$pg['total'] ?> đơn<?= $hasFilter ? ' phù hợp với bộ lọc' : '' ?></div>
        </div>
        <a class="btn btn-buy" href="/showtimes"><i class="bi bi-plus-lg"></i> Đặt vé mới</a>
    </div>

    <form class="panel filter-bar mb-4" method="get" action="/my-bookings">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="f-q">Mã đơn</label>
                <input class="form-control" id="f-q" name="q" value="<?= e($filters['q']) ?>" placeholder="Ví dụ: CB2610...">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="f-status">Trạng thái</label>
                <select class="form-select" id="f-status" name="status" onchange="this.form.submit()"><option value="">Tất cả</option>
                    <?php foreach (['pending', 'confirmed', 'cancelled', 'expired'] as $s): ?><option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(bookingStatusLabel($s)) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="f-from">Đặt từ ngày</label>
                <input class="form-control" type="date" id="f-from" name="date_from" value="<?= e($filters['date_from']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="f-to">Đến ngày</label>
                <input class="form-control" type="date" id="f-to" name="date_to" value="<?= e($filters['date_to']) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-detail flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
                <?php if ($hasFilter): ?><a class="btn btn-outline-light" href="/my-bookings" title="Xóa bộ lọc"><i class="bi bi-x-lg"></i></a><?php endif; ?>
            </div>
        </div>
    </form>

    <?php if (!$rows): ?>
        <div class="panel text-center py-5">
            <div class="display-6 text-muted-2 mb-2"><i class="bi bi-ticket-perforated"></i></div>
            <div class="mb-2"><?= $hasFilter ? 'Không có vé nào phù hợp với bộ lọc.' : 'Bạn chưa đặt vé nào.' ?></div>
            <a class="btn btn-buy" href="/showtimes">Xem lịch chiếu</a>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($rows as $b): $pending = $b['status'] === 'pending' && $b['expires_at'] >= nowString(); ?>
            <div class="col-lg-6">
                <div class="panel booking-card h-100">
                    <div class="poster-thumb" style="<?= $b['poster'] ? "background-image:url('/uploads/" . e($b['poster']) . "')" : '' ?>"></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="mb-0"><?= e($b['movie_title']) ?></h5>
                            <span class="badge fs-6 bg-<?= e(statusBadgeClass($b['status'])) ?>"><?= e(bookingStatusLabel($b['status'])) ?></span>
                        </div>
                        <dl class="info-list">
                            <dt>Mã đơn</dt><dd class="fw-semibold"><?= e($b['booking_code']) ?></dd>
                            <dt>Suất chiếu</dt><dd><?= e(formatDateTime($b['start_time'], 'H:i · d/m/Y')) ?></dd>
                            <dt>Rạp</dt><dd><?= e($b['cinema_name']) ?> – <?= e($b['room_name']) ?></dd>
                            <dt>Ghế</dt><dd><?= e((string)$b['seat_labels']) ?> <span class="text-muted-2">(<?= (int)$b['seat_count'] ?> ghế)</span></dd>
                            <?php if ($pending): ?><dt>Giữ ghế đến</dt><dd class="text-warning"><?= e(formatDateTime($b['expires_at'], 'H:i · d/m')) ?></dd><?php endif; ?>
                        </dl>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top border-secondary-subtle">
                            <span class="fs-5 fw-bold"><?= e(formatVnd((int)$b['total_amount'])) ?></span>
                            <span class="d-flex gap-2">
                                <?php if ($pending): ?><a class="btn btn-sm btn-buy" href="/payment?booking_id=<?= (int)$b['id'] ?>">Thanh toán</a><?php endif; ?>
                                <a class="btn btn-sm btn-detail" href="/booking-detail?id=<?= (int)$b['id'] ?>">Xem chi tiết</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php $basePath = '/my-bookings'; require BASE_PATH . '/app/views/partials/pagination.php'; ?>
</div>
