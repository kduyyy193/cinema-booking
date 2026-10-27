<?php
$isEdit = (int)$showtime['id'] > 0;
$cls = fn(string $k) => isset($errors[$k]) ? 'is-invalid' : '';
$err = fn(string $k) => $errors[$k] ?? '';
$lock = $priceOnly ? 'disabled' : '';
?>
<div style="max-width:720px">
    <h3 class="mb-3"><?= $isEdit ? 'Sửa suất chiếu' : 'Thêm suất chiếu' ?></h3>
    <?php if ($priceOnly): ?><div class="alert alert-warning">Suất này đã có người đặt vé: chỉ được đổi giá.</div><?php endif; ?>
    <form class="adm-panel" method="post" action="<?= $isEdit ? '/admin/showtimes/update' : '/admin/showtimes/store' ?>" novalidate>
        <?= csrfField() ?>
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$showtime['id'] ?>"><?php endif; ?>
        <?php if ($priceOnly): ?>
            <input type="hidden" name="movie_id" value="<?= (int)$showtime['movie_id'] ?>">
            <input type="hidden" name="room_id" value="<?= (int)$showtime['room_id'] ?>">
            <input type="hidden" name="start_time" value="<?= e($showtime['start_time']) ?>">
        <?php endif; ?>
        <div class="mb-3"><label class="form-label">Phim</label>
            <select class="form-select <?= $cls('movie_id') ?>" name="movie_id" <?= $lock ?>>
                <option value="">-- Chọn phim --</option>
                <?php foreach ($movies as $m): ?><option value="<?= (int)$m['id'] ?>" <?= (int)$showtime['movie_id'] === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?>
            </select><div class="invalid-feedback"><?= e($err('movie_id')) ?></div></div>
        <div class="mb-3"><label class="form-label">Phòng chiếu</label>
            <select class="form-select <?= $cls('room_id') ?>" name="room_id" <?= $lock ?>>
                <option value="">-- Chọn phòng --</option>
                <?php foreach ($roomGroups as $cinemaName => $rooms): ?>
                    <optgroup label="<?= e($cinemaName) ?>">
                        <?php foreach ($rooms as $r): ?><option value="<?= (int)$r['id'] ?>" <?= (int)$showtime['room_id'] === (int)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select><div class="invalid-feedback"><?= e($err('room_id')) ?></div></div>
        <div class="mb-3"><label class="form-label">Giờ bắt đầu</label>
            <input class="form-control <?= $cls('start_time') ?>" type="datetime-local" name="start_time" value="<?= e($showtime['start_time']) ?>" <?= $lock ?>>
            <div class="invalid-feedback"><?= e($err('start_time')) ?></div>
            <div class="form-text text-muted-2">Giờ kết thúc = giờ bắt đầu + thời lượng phim + thời gian đệm.</div></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Giá vé thường (VND)</label>
                <input class="form-control <?= $cls('base_price') ?>" name="base_price" inputmode="numeric" value="<?= e($showtime['base_price']) ?>">
                <div class="invalid-feedback"><?= e($err('base_price')) ?></div></div>
            <div class="col-md-6"><label class="form-label">Phụ thu VIP (VND)</label>
                <input class="form-control <?= $cls('vip_surcharge') ?>" name="vip_surcharge" inputmode="numeric" value="<?= e($showtime['vip_surcharge']) ?>">
                <div class="invalid-feedback"><?= e($err('vip_surcharge')) ?></div></div>
        </div>
        <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Lưu</button><a class="btn btn-outline-light" href="/admin/showtimes">Hủy</a></div>
    </form>
</div>
