<div>
    <h3 class="mb-3">Quản lý rạp</h3>
    <form class="adm-panel row g-2 mb-3" method="post" action="/admin/cinemas/store">
        <?= csrfField() ?>
        <div class="col-md-3"><input class="form-control" name="name" maxlength="100" placeholder="Tên rạp" required></div>
        <div class="col-md-4"><input class="form-control" name="address" maxlength="200" placeholder="Địa chỉ"></div>
        <div class="col-md-3"><input class="form-control" name="phone" placeholder="Điện thoại"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Thêm rạp</button></div>
    </form>
    <form class="row g-2 mb-3" method="get" action="/admin/cinemas">
        <div class="col-md-4"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Tìm theo tên rạp..."></div>
        <div class="col-md-2"><button class="btn btn-outline-info w-100">Tìm</button></div>
    </form>
    <div class="adm-panel">
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Tên</th><th>Địa chỉ</th><th>Điện thoại</th><th>Trạng thái</th><th></th></tr></thead>
            <?php if (!$cinemas): ?><tr><td colspan="5" class="text-muted-2">Không có rạp.</td></tr><?php endif; ?>
            <?php foreach ($cinemas as $c): $fid = 'cinema-' . (int)$c['id']; ?>
                <tr>
                    <td>
                        <form id="<?= $fid ?>" method="post" action="/admin/cinemas/update"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"></form>
                        <input class="form-control form-control-sm" form="<?= $fid ?>" name="name" maxlength="100" value="<?= e($c['name']) ?>" required>
                    </td>
                    <td><input class="form-control form-control-sm" form="<?= $fid ?>" name="address" maxlength="200" value="<?= e((string)$c['address']) ?>"></td>
                    <td><input class="form-control form-control-sm" form="<?= $fid ?>" name="phone" value="<?= e((string)$c['phone']) ?>"></td>
                    <td><span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $c['status'] === 'active' ? 'Hoạt động' : 'Tắt' ?></span></td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-outline-info" form="<?= $fid ?>">Lưu</button>
                        <a class="btn btn-sm btn-outline-light" href="/admin/rooms?cinema_id=<?= (int)$c['id'] ?>">Phòng</a>
                        <button class="btn btn-sm btn-outline-warning" form="<?= $fid ?>" formaction="/admin/cinemas/status" formnovalidate data-confirm="<?= $c['status'] === 'active' ? 'Tắt rạp này? Rạp sẽ không hiển thị với khách.' : 'Bật lại rạp này?' ?>" data-confirm-button="<?= $c['status'] === 'active' ? 'Tắt' : 'Bật' ?>"><?= $c['status'] === 'active' ? 'Tắt' : 'Bật' ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
</div>
