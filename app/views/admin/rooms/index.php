<div>
    <h3 class="mb-3">Phòng chiếu</h3>
    <form class="row g-2 mb-3" method="get" action="/admin/rooms">
        <div class="col-md-4"><select class="form-select" name="cinema_id" onchange="this.form.submit()">
            <?php foreach ($cinemas as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cinema && (int)$cinema['id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?><?= $c['status'] === 'inactive' ? ' (tắt)' : '' ?></option><?php endforeach; ?></select></div>
    </form>

    <?php if (!$cinema): ?><p class="text-muted-2">Chưa có rạp nào. Hãy tạo rạp trước.</p><?php else: ?>
    <form class="adm-panel row g-2 mb-3" method="post" action="/admin/rooms/store">
        <?= csrfField() ?><input type="hidden" name="cinema_id" value="<?= (int)$cinema['id'] ?>">
        <div class="col-md-3"><label class="form-label">Tên phòng</label><input class="form-control" name="name" maxlength="100" required></div>
        <div class="col-md-2"><label class="form-label">Số hàng (1–26)</label><input class="form-control" type="number" name="rows" min="1" max="26" value="8" required></div>
        <div class="col-md-2"><label class="form-label">Số cột (1–30)</label><input class="form-control" type="number" name="cols" min="1" max="30" value="10" required></div>
        <div class="col-md-3"><label class="form-label">Số hàng cuối là VIP</label><input class="form-control" type="number" name="vip_rows" min="0" max="26" value="2" required></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Tạo phòng</button></div>
    </form>

    <div class="adm-panel">
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Tên</th><th>Hàng</th><th>Cột</th><th>Ghế (VIP/khóa)</th><th>Trạng thái</th><th></th></tr></thead>
            <?php if (!$rooms): ?><tr><td colspan="6" class="text-muted-2">Rạp chưa có phòng.</td></tr><?php endif; ?>
            <?php foreach ($rooms as $r): $fid = 'room-' . (int)$r['id']; $sum = $r['summary']; ?>
                <tr>
                    <td>
                        <form id="<?= $fid ?>" method="post" action="/admin/rooms/update"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="vip_rows" value="0"></form>
                        <input class="form-control form-control-sm" form="<?= $fid ?>" name="name" maxlength="100" value="<?= e($r['name']) ?>" required>
                    </td>
                    <td><input class="form-control form-control-sm" style="width:80px" type="number" form="<?= $fid ?>" name="rows" min="1" max="26" value="<?= (int)$sum['rows'] ?>" <?= $r['has_bookings'] ? 'readonly' : '' ?>></td>
                    <td><input class="form-control form-control-sm" style="width:80px" type="number" form="<?= $fid ?>" name="cols" min="1" max="30" value="<?= (int)$sum['cols'] ?>" <?= $r['has_bookings'] ? 'readonly' : '' ?>></td>
                    <td><?= (int)$sum['total'] ?> <small class="text-muted-2">(<?= (int)$sum['vip'] ?> VIP, <?= (int)$sum['locked'] ?> khóa)</small></td>
                    <td><select class="form-select form-select-sm" form="<?= $fid ?>" name="status">
                        <option value="active" <?= $r['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $r['status'] === 'inactive' ? 'selected' : '' ?>>Tắt</option></select></td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-outline-info" form="<?= $fid ?>">Lưu</button>
                        <a class="btn btn-sm btn-outline-light" href="/admin/seats?room_id=<?= (int)$r['id'] ?>">Ghế</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
        <div class="small text-muted-2 mt-2">Phòng đã có lịch sử đặt vé chỉ được đổi tên và trạng thái.</div>
    </div>
    <?php endif; ?>
</div>
