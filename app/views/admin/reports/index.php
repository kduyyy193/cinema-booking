<?php $qs = fn(string $type) => http_build_query(['from' => $from, 'to' => $to, 'type' => $type]); ?>
<div>
    <h3 class="mb-3">Báo cáo doanh thu</h3>
    <form class="row g-2 mb-3 align-items-end" method="get" action="/admin/reports">
        <div class="col-md-3"><label class="form-label">Từ ngày</label><input class="form-control" type="date" name="from" value="<?= e($from) ?>"></div>
        <div class="col-md-3"><label class="form-label">Đến ngày</label><input class="form-control" type="date" name="to" value="<?= e($to) ?>"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Xem báo cáo</button></div>
    </form>
    <?php foreach ($errors as $msg): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endforeach; ?>

    <?php if (!$errors): ?>
    <div class="row g-3 mb-3">
        <?php
        $avg = $summary['orders'] > 0 ? intdiv($summary['revenue'], $summary['orders']) : 0;
        foreach ([
            ['Tổng doanh thu', formatVnd($summary['revenue'])],
            ['Số đơn', (string)$summary['orders']],
            ['Số vé', (string)$summary['tickets']],
            ['Giá trị trung bình / đơn', formatVnd($avg)],
        ] as [$label, $value]): ?>
            <div class="col-6 col-lg-3"><div class="stat-card"><div class="label"><?= e($label) ?></div><div class="value"><?= e($value) ?></div></div></div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="adm-panel">
                <h6 class="mb-3">Doanh thu theo ngày</h6>
                <canvas id="revenue-chart" height="90" data-chart="<?= e(json_encode($byDate, JSON_UNESCAPED_UNICODE)) ?>"></canvas>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="adm-panel h-100">
                <h6 class="mb-3">Doanh thu theo phim</h6>
                <?php if (!$byMovie): ?><p class="text-muted-2 small mb-0">Không có dữ liệu.</p><?php endif; ?>
                <canvas id="movie-chart" height="<?= max(80, count($byMovie) * 28) ?>" data-chart="<?= e(json_encode($byMovie, JSON_UNESCAPED_UNICODE)) ?>"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="adm-panel h-100">
                <h6 class="mb-3">Tỷ trọng theo rạp</h6>
                <?php if (!$byCinema): ?><p class="text-muted-2 small mb-0">Không có dữ liệu.</p><?php endif; ?>
                <canvas id="cinema-chart" height="200" data-chart="<?= e(json_encode($byCinema, JSON_UNESCAPED_UNICODE)) ?>"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="adm-panel h-100">
                <div class="d-flex justify-content-between mb-2"><h6>Theo ngày</h6><a class="btn btn-sm btn-outline-info" href="/admin/reports/export?<?= e($qs('date')) ?>">Xuất CSV</a></div>
                <div style="max-height:420px;overflow:auto"><table class="table table-sm mb-0">
                    <thead><tr><th>Ngày</th><th class="text-end">Đơn</th><th class="text-end">Doanh thu</th></tr></thead>
                    <?php foreach ($byDate as $d): ?><tr><td><?= e(date('d/m/Y', strtotime($d['day']))) ?></td><td class="text-end"><?= (int)$d['orders'] ?></td><td class="text-end"><?= e(formatVnd($d['revenue'])) ?></td></tr><?php endforeach; ?>
                </table></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="adm-panel mb-3">
                <div class="d-flex justify-content-between mb-2"><h6>Theo phim (top bán chạy)</h6><a class="btn btn-sm btn-outline-info" href="/admin/reports/export?<?= e($qs('movie')) ?>">Xuất CSV</a></div>
                <?php if (!$byMovie): ?><p class="text-muted-2 small mb-0">Không có dữ liệu.</p><?php endif; ?>
                <table class="table table-sm mb-0">
                    <?php foreach ($byMovie as $m): ?><tr><td><?= e($m['name']) ?></td><td class="text-end"><?= (int)$m['tickets'] ?> vé</td><td class="text-end"><?= e(formatVnd((int)$m['revenue'])) ?></td></tr><?php endforeach; ?>
                </table>
            </div>
            <div class="adm-panel">
                <div class="d-flex justify-content-between mb-2"><h6>Theo rạp</h6><a class="btn btn-sm btn-outline-info" href="/admin/reports/export?<?= e($qs('cinema')) ?>">Xuất CSV</a></div>
                <?php if (!$byCinema): ?><p class="text-muted-2 small mb-0">Không có dữ liệu.</p><?php endif; ?>
                <table class="table table-sm mb-0">
                    <?php foreach ($byCinema as $c): ?><tr><td><?= e($c['name']) ?></td><td class="text-end"><?= (int)$c['tickets'] ?> vé</td><td class="text-end"><?= e(formatVnd((int)$c['revenue'])) ?></td></tr><?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
