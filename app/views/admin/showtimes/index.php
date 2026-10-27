<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Suất chiếu</h3>
        <a class="btn btn-primary" href="/admin/showtimes/create"><i class="bi bi-plus-lg"></i> Thêm suất</a>
    </div>
    <form class="row g-2 mb-3" method="get" action="/admin/showtimes">
        <div class="col-md-2"><input class="form-control" type="date" name="date" value="<?= e($filters['date']) ?>"></div>
        <div class="col-md-3"><select class="form-select" name="movie_id" onchange="this.form.submit()"><option value="">Mọi phim</option>
            <?php foreach ($movies as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $filters['movie_id'] === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select" name="cinema_id" onchange="this.form.submit()"><option value="">Mọi rạp</option>
            <?php foreach ($cinemas as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $filters['cinema_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">Mọi trạng thái</option>
            <option value="scheduled" <?= $filters['status'] === 'scheduled' ? 'selected' : '' ?>>Đang mở</option>
            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option></select></div>
        <div class="col-md-2"><button class="btn btn-outline-info w-100">Lọc</button></div>
    </form>
    <div class="adm-panel">
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Giờ chiếu</th><th>Phim</th><th>Rạp – Phòng</th><th>Giá</th><th>Đã bán</th><th>Trạng thái</th><th></th></tr></thead>
            <?php if (!$rows): ?><tr><td colspan="7" class="text-muted-2">Không có suất chiếu.</td></tr><?php endif; ?>
            <?php foreach ($rows as $s): ?>
                <tr>
                    <td><?= e(formatDateTime($s['start_time'])) ?><br><small class="text-muted-2">đến <?= e(formatDateTime($s['end_time'], 'H:i')) ?></small></td>
                    <td><?= e($s['movie_title']) ?></td>
                    <td><?= e($s['cinema_name']) ?> – <?= e($s['room_name']) ?></td>
                    <td><?= e(formatVnd((int)$s['base_price'])) ?><br><small class="text-muted-2">VIP +<?= e(formatVnd((int)$s['vip_surcharge'])) ?></small></td>
                    <td><?= (int)$s['sold'] ?></td>
                    <td><span class="badge bg-<?= $s['status'] === 'scheduled' ? 'success' : 'secondary' ?>"><?= $s['status'] === 'scheduled' ? 'Đang mở' : 'Đã hủy' ?></span></td>
                    <td class="text-end text-nowrap">
                        <?php if ($s['status'] === 'scheduled'): ?>
                            <a class="btn btn-sm btn-outline-info" href="/admin/showtimes/edit?id=<?= (int)$s['id'] ?>">Sửa</a>
                            <form class="d-inline" method="post" action="/admin/showtimes/cancel" onsubmit="return confirm('Hủy suất chiếu? Các đơn liên quan sẽ bị hủy và hoàn tiền giả lập.')">
                                <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button class="btn btn-sm btn-outline-danger">Hủy suất</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php $basePath = '/admin/showtimes'; require BASE_PATH . '/app/views/partials/pagination.php'; ?>
</div>
