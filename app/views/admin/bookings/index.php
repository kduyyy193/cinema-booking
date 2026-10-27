<div>
    <h3 class="mb-3">Đơn vé</h3>
    <form class="row g-2 mb-3" method="get" action="/admin/bookings">
        <div class="col-md-3"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Mã đơn hoặc email khách"></div>
        <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="<?= e($filters['date_from']) ?>" title="Từ ngày"></div>
        <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="<?= e($filters['date_to']) ?>" title="Đến ngày"></div>
        <div class="col-md-2"><select class="form-select" name="movie_id" onchange="this.form.submit()"><option value="">Mọi phim</option>
            <?php foreach ($movies as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $filters['movie_id'] === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">Mọi trạng thái</option>
            <?php foreach (['pending', 'confirmed', 'cancelled', 'expired'] as $s): ?><option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(bookingStatusLabel($s)) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-1"><button class="btn btn-outline-info w-100">Lọc</button></div>
    </form>
    <div class="adm-panel">
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Mã đơn</th><th>Khách</th><th>Phim</th><th>Giờ chiếu</th><th class="text-end">Tổng tiền</th><th>Đơn</th><th>Thanh toán</th><th></th></tr></thead>
            <?php if (!$rows): ?><tr><td colspan="8" class="text-muted-2">Không có đơn phù hợp.</td></tr><?php endif; ?>
            <?php foreach ($rows as $b): ?>
                <tr>
                    <td><?= e($b['booking_code']) ?><br><small class="text-muted-2"><?= e(formatDateTime($b['created_at'])) ?></small></td>
                    <td><?= e($b['user_name']) ?><br><small class="text-muted-2"><?= e($b['user_email']) ?></small></td>
                    <td><?= e($b['movie_title']) ?></td>
                    <td><?= e(formatDateTime($b['start_time'])) ?></td>
                    <td class="text-end"><?= e(formatVnd((int)$b['total_amount'])) ?></td>
                    <td><span class="badge bg-<?= e(statusBadgeClass($b['status'])) ?>"><?= e(bookingStatusLabel($b['status'])) ?></span></td>
                    <td><?php if ($b['payment_status']): ?><span class="badge bg-<?= e(statusBadgeClass($b['payment_status'])) ?>"><?= e(paymentStatusLabel($b['payment_status'])) ?></span><?php endif; ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-info" href="/admin/bookings/show?id=<?= (int)$b['id'] ?>">Chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php $basePath = '/admin/bookings'; require BASE_PATH . '/app/views/partials/pagination.php'; ?>
</div>
