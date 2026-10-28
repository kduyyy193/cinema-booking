<div style="max-width:720px">
    <h3 class="mb-3">Thể loại</h3>
    <form class="adm-panel d-flex gap-2 mb-3" method="post" action="/admin/genres/store">
        <?= csrfField() ?>
        <input class="form-control" name="name" maxlength="50" placeholder="Tên thể loại mới" required>
        <button class="btn btn-primary text-nowrap">Thêm</button>
    </form>
    <div class="adm-panel">
        <table class="table align-middle mb-0">
            <thead><tr><th>Tên</th><th>Số phim</th><th></th></tr></thead>
            <?php foreach ($genres as $g): ?>
                <tr>
                    <td>
                        <form class="d-flex gap-2" method="post" action="/admin/genres/update">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                            <input class="form-control form-control-sm" name="name" maxlength="50" value="<?= e($g['name']) ?>" required>
                            <button class="btn btn-sm btn-outline-info">Lưu</button>
                        </form>
                    </td>
                    <td><?= (int)$g['movie_count'] ?></td>
                    <td class="text-end">
                        <form method="post" action="/admin/genres/delete" data-confirm="Xóa thể loại này?" data-confirm-button="Xóa">
                            <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
