<div>
    <h3 class="mb-3">Ghế</h3>
    <form class="row g-2 mb-3" method="get" action="/admin/seats">
        <div class="col-md-4"><select class="form-select" onchange="window.location='/admin/rooms?cinema_id='+this.value">
            <?php foreach ($cinemas as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $room && (int)$room['cinema_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><select class="form-select" name="room_id" onchange="this.form.submit()">
            <?php foreach ($rooms as $r): ?><option value="<?= (int)$r['id'] ?>" <?= $room && (int)$room['id'] === (int)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></div>
    </form>

    <?php if (!$room): ?><p class="text-muted-2">Chưa có phòng nào.</p><?php else: ?>
    <div class="adm-panel">
        <h6 class="mb-3"><?= e($room['cinema_name']) ?> – <?= e($room['name']) ?></h6>
        <div class="screen-curve" style="max-width:600px">MÀN HÌNH</div>
        <div class="seat-grid">
            <?php foreach ($rows as $label => $seats): ?>
                <div class="seat-row">
                    <span class="row-label"><?= e($label) ?></span>
                    <?php foreach ($seats as $s): $tag = $s['seat_row'] . $s['seat_number']; ?>
                        <div class="admin-seat <?= $s['seat_type'] === 'vip' ? 'vip' : '' ?> <?= $s['status'] === 'locked' ? 'locked' : '' ?>">
                            <div class="seat-tag"><?= (int)$s['seat_number'] ?></div>
                            <form method="post" action="/admin/seats/update"><?= csrfField() ?>
                                <input type="hidden" name="seat_id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="type" value="<?= $s['seat_type'] === 'vip' ? 'standard' : 'vip' ?>">
                                <button title="Đổi loại <?= e($tag) ?> sang <?= $s['seat_type'] === 'vip' ? 'thường' : 'VIP' ?>"><i class="bi bi-star<?= $s['seat_type'] === 'vip' ? '-fill' : '' ?>"></i></button></form>
                            <form method="post" action="/admin/seats/lock"><?= csrfField() ?>
                                <input type="hidden" name="seat_id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="locked" value="<?= $s['status'] === 'locked' ? '0' : '1' ?>">
                                <button title="<?= $s['status'] === 'locked' ? 'Mở' : 'Khóa' ?> ghế <?= e($tag) ?>"><i class="bi bi-<?= $s['status'] === 'locked' ? 'unlock' : 'lock' ?>"></i></button></form>
                        </div>
                    <?php endforeach; ?>
                    <span class="row-label"><?= e($label) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="small text-muted-2 mt-3"><i class="bi bi-star-fill text-warning"></i> VIP · ghế xám là ghế đang khóa · ghế đang có đơn ở suất chưa chiếu không thể khóa.</div>
    </div>
    <?php endif; ?>
</div>
