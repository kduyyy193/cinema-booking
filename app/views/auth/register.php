<?php $f = fn($k) => e($old[$k] ?? ''); $err = fn($k) => isset($errors[$k]) ? '<div class="text-danger small mt-1">' . e($errors[$k]) . '</div>' : ''; ?>
<div class="container py-5" style="max-width:440px">
    <div class="panel">
        <h4 class="mb-3">Đăng ký</h4>
        <form method="post" action="/register">
            <?= csrfField() ?>
            <div class="mb-3"><label class="form-label">Họ tên</label><input class="form-control" name="name" value="<?= $f('name') ?>" required><?= $err('name') ?></div>
            <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= $f('email') ?>" required><?= $err('email') ?></div>
            <div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required><?= $err('password') ?></div>
            <div class="mb-3"><label class="form-label">Xác nhận mật khẩu</label><input class="form-control" type="password" name="password_confirm" required><?= $err('password_confirm') ?></div>
            <button class="btn btn-buy w-100">Tạo tài khoản</button>
        </form>
        <p class="small text-muted-2 mt-3 mb-0">Đã có tài khoản? <a href="/login">Đăng nhập</a></p>
    </div>
</div>
