<div class="container py-4">
    <h3 class="mb-3">Lịch chiếu</h3>
    <div class="date-tabs mb-3">
        <?php foreach ($days as $d): $qs = http_build_query(array_filter(['date' => $d['date'], 'movie' => $movieId, 'cinema' => $cinemaId])); ?>
            <a href="/showtimes?<?= e($qs) ?>" class="<?= $d['date'] === $date ? 'active' : '' ?>"><?= e($d['label']) ?><small><?= e($d['sub']) ?></small></a>
        <?php endforeach; ?>
    </div>
    <form class="panel filter-bar mb-4" method="get" action="/showtimes">
        <input type="hidden" name="date" value="<?= e($date) ?>">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="f-movie">Phim</label>
                <select class="form-select" id="f-movie" name="movie" onchange="this.form.submit()"><option value="">Tất cả phim</option>
                    <?php foreach ($movies as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $movieId === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="f-cinema">Rạp</label>
                <select class="form-select" id="f-cinema" name="cinema" onchange="this.form.submit()"><option value="">Tất cả rạp</option>
                    <?php foreach ($cinemas as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cinemaId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <noscript><button class="btn btn-detail flex-grow-1">Lọc</button></noscript>
                <?php if ($movieId || $cinemaId): ?><a class="btn btn-outline-light w-100" href="/showtimes?date=<?= e($date) ?>"><i class="bi bi-x-lg"></i> Xóa lọc</a><?php endif; ?>
            </div>
        </div>
    </form>

    <div class="small text-muted-2 mb-2"><?= count($rows) ?> suất chiếu ngày <?= e(date('d/m/Y', strtotime($date))) ?></div>
    <?php if (!$rows): ?><div class="panel text-center text-muted-2">Không có suất chiếu phù hợp. Hãy thử chọn ngày khác hoặc bỏ bớt bộ lọc.</div><?php endif; ?>
    <?php if ($rows): ?>
    <div class="table-responsive"><table class="table table-dark align-middle">
        <thead><tr><th>Giờ</th><th>Phim</th><th>Rạp – Phòng</th><th>Giá</th><th>Ghế còn</th><th></th></tr></thead>
        <?php foreach ($rows as $s): ?>
            <tr>
                <td class="fw-semibold"><?= e(date('H:i', strtotime($s['start_time']))) ?></td>
                <td><a class="text-decoration-none" href="/movie?id=<?= (int)$s['movie_id'] ?>"><?= e($s['movie_title']) ?></a></td>
                <td><?= e($s['cinema_name']) ?> – <?= e($s['room_name']) ?></td>
                <td><?= e(formatVnd((int)$s['base_price'])) ?><?php if ((int)$s['vip_surcharge'] > 0): ?><br><small class="text-muted-2">VIP +<?= e(formatVnd((int)$s['vip_surcharge'])) ?></small><?php endif; ?></td>
                <td><?= (int)$s['seats_left'] ?></td>
                <td class="text-end">
                    <?php if ($s['state'] === 'open'): ?>
                        <a class="btn btn-sm btn-buy" href="/select-seats?showtime=<?= (int)$s['id'] ?>">Chọn ghế</a>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= ['cancelled' => 'Đã hủy', 'finished' => 'Đã chiếu', 'soldout' => 'Hết vé'][$s['state']] ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
</div>
