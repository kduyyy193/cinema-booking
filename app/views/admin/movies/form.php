<?php
$isEdit = (int)$movie['id'] > 0;
$err = fn(string $k) => $errors[$k] ?? '';
$cls = fn(string $k) => isset($errors[$k]) ? 'is-invalid' : '';
$selectedIds = array_map('intval', $selected);
?>
<div style="max-width:880px">
    <h3 class="mb-3"><?= $isEdit ? 'Sửa phim' : 'Thêm phim' ?></h3>
    <form class="adm-panel" method="post" action="<?= $isEdit ? '/admin/movies/update' : '/admin/movies/store' ?>" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$movie['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Tên phim</label>
                <input class="form-control <?= $cls('title') ?>" name="title" maxlength="200" value="<?= e($movie['title']) ?>" required>
                <div class="invalid-feedback"><?= e($err('title')) ?></div></div>
            <div class="col-md-4"><label class="form-label">Thời lượng (phút)</label>
                <input class="form-control <?= $cls('duration') ?>" name="duration" inputmode="numeric" value="<?= e($movie['duration']) ?>" required>
                <div class="invalid-feedback"><?= e($err('duration')) ?></div></div>
            <div class="col-md-4"><label class="form-label">Ngày khởi chiếu</label>
                <input class="form-control <?= $cls('release_date') ?>" type="date" name="release_date" value="<?= e($movie['release_date']) ?>" required>
                <div class="invalid-feedback"><?= e($err('release_date')) ?></div></div>
            <div class="col-md-4"><label class="form-label">Phân loại</label>
                <select class="form-select <?= $cls('age_rating') ?>" name="age_rating"><?php foreach (ageRatings() as $a): ?><option <?= $movie['age_rating'] === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?></select>
                <div class="invalid-feedback"><?= e($err('age_rating')) ?></div></div>
            <div class="col-md-4"><label class="form-label">Trạng thái</label>
                <select class="form-select <?= $cls('status') ?>" name="status">
                    <?php foreach (['now_showing' => 'Đang chiếu', 'upcoming' => 'Sắp chiếu', 'hidden' => 'Ẩn'] as $k => $v): ?><option value="<?= $k ?>" <?= $movie['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
                <div class="invalid-feedback"><?= e($err('status')) ?></div></div>
            <div class="col-12"><label class="form-label">Thể loại (chọn nhiều)</label>
                <select class="form-select <?= $cls('genre_ids') ?>" name="genre_ids[]" multiple size="5">
                    <?php foreach ($genres as $g): ?><option value="<?= (int)$g['id'] ?>" <?= in_array((int)$g['id'], $selectedIds, true) ? 'selected' : '' ?>><?= e($g['name']) ?></option><?php endforeach; ?></select>
                <div class="invalid-feedback"><?= e($err('genre_ids')) ?></div></div>
            <div class="col-md-6"><label class="form-label">Đạo diễn</label><input class="form-control" name="director" value="<?= e($movie['director']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Diễn viên</label><input class="form-control" name="cast_list" value="<?= e($movie['cast_list']) ?>"></div>
            <div class="col-12"><label class="form-label">Trailer (URL http/https)</label>
                <input class="form-control <?= $cls('trailer_url') ?>" name="trailer_url" value="<?= e((string)$movie['trailer_url']) ?>">
                <div class="invalid-feedback"><?= e($err('trailer_url')) ?></div></div>
            <div class="col-12"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" rows="4"><?= e($movie['description']) ?></textarea></div>
            <div class="col-12"><label class="form-label">Poster (JPG/PNG/WEBP, tối đa 2 MB)</label>
                <div class="d-flex gap-3 align-items-start">
                    <?php if ($movie['poster']): ?><img src="/uploads/<?= e($movie['poster']) ?>" alt="Poster hiện tại" style="width:80px;border-radius:6px"><?php endif; ?>
                    <div class="flex-grow-1"><input class="form-control <?= $cls('poster') ?>" type="file" name="poster" accept="image/jpeg,image/png,image/webp">
                        <div class="invalid-feedback"><?= e($err('poster')) ?></div></div>
                </div></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Lưu</button><a class="btn btn-outline-light" href="/admin/movies">Hủy</a></div>
    </form>
</div>
