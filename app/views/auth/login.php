<div class="container py-5" style="max-width:440px">
    <div class="panel">
        <h4 class="mb-3">Đăng nhập</h4>
        <form method="post" action="/login">
            <?= csrfField() ?>
            <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required autofocus></div>
            <div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required></div>
            <button class="btn btn-buy w-100">Đăng nhập</button>
        </form>
        <p class="small text-muted-2 mt-3 mb-0">Chưa có tài khoản? <a href="/register">Đăng ký</a></p>
    </div>
</div>
