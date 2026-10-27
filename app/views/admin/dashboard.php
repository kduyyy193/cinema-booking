<div>
    <h3 class="mb-3">Dashboard</h3>
    <div class="row g-3 mb-4">
        <?php foreach ([
            ['Phim đang hiển thị', (string)$totals['movies']],
            ['Suất chiếu sắp tới', (string)$totals['upcoming_shows']],
            ['Tổng đơn', (string)$totals['bookings']],
            ['Đơn đã xác nhận', (string)$totals['confirmed']],
            ['Doanh thu hôm nay', formatVnd($totals['revenue_today'])],
            ['Doanh thu tháng này', formatVnd($totals['revenue_month'])],
        ] as [$label, $value]): ?>
            <div class="col-6 col-lg-2"><div class="stat-card"><div class="label"><?= e($label) ?></div><div class="value"><?= e($value) ?></div></div></div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="adm-panel h-100">
                <h6 class="mb-3">Doanh thu 7 ngày gần nhất</h6>
                <canvas id="revenue-chart" height="110" data-chart="<?= e(json_encode($chart, JSON_UNESCAPED_UNICODE)) ?>"></canvas>
                <table class="table table-sm mt-3 mb-0">
                    <?php foreach ($chart as $d): ?>
                        <tr><td><?= e(date('d/m', strtotime($d['day']))) ?></td><td><?= (int)$d['orders'] ?> đơn</td><td class="text-end"><?= e(formatVnd($d['revenue'])) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="adm-panel h-100">
                <h6 class="mb-3">Phim bán chạy</h6>
                <?php if (!$popular): ?><p class="text-muted-2 small mb-0">Chưa có dữ liệu.</p><?php endif; ?>
                <table class="table table-sm mb-0">
                    <?php foreach ($popular as $p): ?>
                        <tr><td><?= e($p['title']) ?></td><td class="text-end"><?= (int)$p['tickets'] ?> vé</td><td class="text-end"><?= e(formatVnd((int)$p['revenue'])) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="adm-panel h-100">
                <h6 class="mb-3">Đơn gần nhất</h6>
                <table class="table table-sm align-middle mb-0">
                    <?php foreach ($recent as $b): ?>
                        <tr>
                            <td><a class="text-decoration-none" href="/admin/bookings/show?id=<?= (int)$b['id'] ?>"><?= e($b['booking_code']) ?></a><br><small class="text-muted-2"><?= e($b['user_name']) ?> · <?= e($b['movie_title']) ?></small></td>
                            <td class="text-end"><?= e(formatVnd((int)$b['total_amount'])) ?></td>
                            <td class="text-end"><span class="badge bg-<?= e(statusBadgeClass($b['status'])) ?>"><?= e(bookingStatusLabel($b['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="adm-panel h-100">
                <h6 class="mb-3">Suất chiếu sắp diễn ra</h6>
                <table class="table table-sm align-middle mb-0">
                    <?php foreach ($upcoming as $s): ?>
                        <tr>
                            <td><?= e($s['movie_title']) ?><br><small class="text-muted-2"><?= e($s['cinema_name']) ?> – <?= e($s['room_name']) ?></small></td>
                            <td><?= e(formatDateTime($s['start_time'], 'd/m H:i')) ?></td>
                            <td class="text-end"><?= (int)$s['sold'] ?>/<?= (int)$s['capacity'] ?> ghế</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>
