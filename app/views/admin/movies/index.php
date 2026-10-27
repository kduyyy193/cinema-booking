<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Quản lý phim</h3>
        <a class="btn btn-primary" href="/admin/movies/create"><i class="bi bi-plus-lg"></i> Thêm phim</a>
    </div>
    <form class="row g-2 mb-3" method="get" action="/admin/movies">
        <div class="col-md-4"><input class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Tên phim..."></div>
        <div class="col-md-3"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">Mọi trạng thái</option>
            <?php foreach (['now_showing' => 'Đang chiếu', 'upcoming' => 'Sắp chiếu', 'hidden' => 'Đã ẩn'] as $k => $v): ?><option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select" name="genre_id" onchange="this.form.submit()"><option value="">Mọi thể loại</option>
            <?php foreach ($genres as $g): ?><option value="<?= (int)$g['id'] ?>" <?= $filters['genre_id'] === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-outline-info w-100">Lọc</button></div>
    </form>
    <div class="adm-panel">
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th></th><th>Tên phim</th><th>Thể loại</th><th>Thời lượng</th><th>Khởi chiếu</th><th>Trạng thái</th><th></th></tr></thead>
            <?php if (!$rows): ?><tr><td colspan="7" class="text-muted-2">Không có phim.</td></tr><?php endif; ?>
            <?php foreach ($rows as $m): ?>
                <tr>
                    <td><div style="width:40px;aspect-ratio:2/3;border-radius:4px;background:#34344f center/cover;<?= $m['poster'] ? "background-image:url('/uploads/" . e($m['poster']) . "')" : '' ?>"></div></td>
                    <td><?= e($m['title']) ?></td>
                    <td class="small text-muted-2"><?= e((string)$m['genre_names']) ?></td>
                    <td><?= (int)$m['duration'] ?>'</td>
                    <td><?= e($m['release_date']) ?></td>
                    <td><span class="badge bg-<?= ['now_showing' => 'success', 'upcoming' => 'info', 'hidden' => 'secondary'][$m['status']] ?>"><?= ['now_showing' => 'Đang chiếu', 'upcoming' => 'Sắp chiếu', 'hidden' => 'Đã ẩn'][$m['status']] ?></span></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-info" href="/admin/movies/edit?id=<?= (int)$m['id'] ?>">Sửa</a>
                        <form class="d-inline" method="post" action="/admin/movies/status"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button class="btn btn-sm btn-outline-warning"><?= $m['status'] === 'hidden' ? 'Hiện' : 'Ẩn' ?></button></form>
                        <form class="d-inline" method="post" action="/admin/movies/delete" onsubmit="return confirm('Xóa phim này?')"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php $basePath = '/admin/movies'; require BASE_PATH . '/app/views/partials/pagination.php'; ?>
</div>
