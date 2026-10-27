<?php
$fields = [
    'site_name' => ['Tên website', 'text', 'Tối đa 60 ký tự.'],
    'cancel_before_minutes' => ['Hạn hủy vé đã thanh toán (phút trước giờ chiếu)', 'number', '0 – 10080.'],
    'showtime_buffer_minutes' => ['Thời gian đệm sau mỗi suất (phút)', 'number', '0 – 120.'],
    'hold_minutes' => ['Thời gian giữ ghế chờ thanh toán (phút)', 'number', '1 – 60.'],
];
?>
<div style="max-width:640px">
    <h3 class="mb-3">Cài đặt hệ thống</h3>
    <div class="adm-panel">
        <form method="post" action="/admin/settings" novalidate>
            <?= csrfField() ?>
            <?php foreach ($fields as $key => [$label, $type, $hint]): ?>
                <div class="mb-3">
                    <label class="form-label"><?= e($label) ?></label>
                    <input class="form-control <?= isset($errors[$key]) ? 'is-invalid' : '' ?>" type="<?= e($type) ?>" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>">
                    <div class="invalid-feedback"><?= e($errors[$key] ?? '') ?></div>
                    <div class="form-text text-muted-2"><?= e($hint) ?></div>
                </div>
            <?php endforeach; ?>
            <button class="btn btn-primary">Lưu cài đặt</button>
        </form>
    </div>
</div>
