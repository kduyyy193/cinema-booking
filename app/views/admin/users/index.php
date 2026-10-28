<div>
    <h3 class="mb-3">Người dùng</h3>
    <form class="row g-2 mb-3" method="get" action="/admin/users">
        <div class="col-md-5"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Tên hoặc email..."></div>
        <div class="col-md-3"><select class="form-select" name="role" onchange="this.form.submit()"><option value="">Mọi vai trò</option>
            <option value="customer" <?= $filters['role'] === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
            <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Quản trị</option></select></div>
        <div class="col-md-2"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">Mọi trạng thái</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
            <option value="locked" <?= $filters['status'] === 'locked' ? 'selected' : '' ?>>Đã khóa</option></select></div>
        <div class="col-md-2"><button class="btn btn-outline-info w-100">Lọc</button></div>
    </form>
    <div class="adm-panel">
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Tên</th><th>Email</th><th>Điện thoại</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th></th></tr></thead>
            <?php foreach ($rows as $u): $self = (int)$u['id'] === $selfId; ?>
                <tr>
                    <td><?= e($u['name']) ?><?= $self ? ' <span class="badge bg-info">Bạn</span>' : '' ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e((string)$u['phone']) ?></td>
                    <td>
                        <form class="d-flex gap-1" method="post" action="/admin/users/role">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <select class="form-select form-select-sm" name="role" <?= $self ? 'disabled' : '' ?>>
                                <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Quản trị</option></select>
                            <?php if (!$self): ?><button class="btn btn-sm btn-outline-info">Đổi</button><?php endif; ?>
                        </form>
                    </td>
                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= $u['status'] === 'active' ? 'Hoạt động' : 'Đã khóa' ?></span></td>
                    <td><?= e(formatDateTime($u['created_at'], 'd/m/Y')) ?></td>
                    <td class="text-end">
                        <?php if (!$self): ?>
                            <form method="post" action="/admin/users/status" data-confirm="<?= $u['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?> tài khoản này?" data-confirm-button="<?= $u['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?>">
                                <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button class="btn btn-sm btn-outline-<?= $u['status'] === 'active' ? 'danger' : 'success' ?>"><?= $u['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php $basePath = '/admin/users'; require BASE_PATH . '/app/views/partials/pagination.php'; ?>
</div>
