<?php
$val = fn(string $k, $default = '') => $old[$k] ?? $default;
$invalid = fn(array $errs, string $k) => isset($errs[$k]) ? 'is-invalid' : '';
?>
<div class="container py-4" style="max-width:860px">
    <h3 class="mb-3">Hồ sơ cá nhân</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="panel">
                <h5 class="mb-3">Thông tin</h5>
                <form method="post" action="/profile" novalidate>
                    <?= csrfField() ?>
                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" value="<?= e($account['email']) ?>" readonly></div>
                    <div class="mb-3"><label class="form-label">Họ tên</label>
                        <input class="form-control <?= $invalid($profileErrors, 'name') ?>" name="name" maxlength="100" value="<?= e($val('name', $account['name'])) ?>" required>
                        <div class="invalid-feedback"><?= e($profileErrors['name'] ?? '') ?></div></div>
                    <div class="mb-3"><label class="form-label">Số điện thoại</label>
                        <input class="form-control <?= $invalid($profileErrors, 'phone') ?>" name="phone" value="<?= e($val('phone', (string)$account['phone'])) ?>">
                        <div class="invalid-feedback"><?= e($profileErrors['phone'] ?? '') ?></div></div>
                    <button class="btn btn-buy">Lưu thay đổi</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel">
                <h5 class="mb-3">Đổi mật khẩu</h5>
                <form method="post" action="/profile/password" novalidate>
                    <?= csrfField() ?>
                    <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label>
                        <input class="form-control <?= $invalid($passwordErrors, 'current_password') ?>" type="password" name="current_password" autocomplete="current-password" required>
                        <div class="invalid-feedback"><?= e($passwordErrors['current_password'] ?? '') ?></div></div>
                    <div class="mb-3"><label class="form-label">Mật khẩu mới</label>
                        <input class="form-control <?= $invalid($passwordErrors, 'password') ?>" type="password" name="password" autocomplete="new-password" required>
                        <div class="invalid-feedback"><?= e($passwordErrors['password'] ?? '') ?></div></div>
                    <div class="mb-3"><label class="form-label">Xác nhận mật khẩu mới</label>
                        <input class="form-control <?= $invalid($passwordErrors, 'password_confirm') ?>" type="password" name="password_confirm" autocomplete="new-password" required>
                        <div class="invalid-feedback"><?= e($passwordErrors['password_confirm'] ?? '') ?></div></div>
                    <button class="btn btn-detail">Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>
