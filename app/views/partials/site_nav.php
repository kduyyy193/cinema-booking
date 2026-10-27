<?php $user = currentUser(); $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<nav class="site-nav py-2">
    <div class="container d-flex flex-wrap align-items-center gap-2 gap-md-4">
        <a class="brand" href="/"><i class="bi bi-film text-danger"></i> <?= e(siteName()) ?></a>
        <ul class="nav d-none d-md-flex">
            <li><a class="nav-link <?= $path === '/' ? 'active' : '' ?>" href="/">Trang chủ</a></li>
            <li><a class="nav-link <?= str_starts_with($path, '/movie') ? 'active' : '' ?>" href="/movies">Phim</a></li>
            <li><a class="nav-link <?= str_starts_with($path, '/showtimes') ? 'active' : '' ?>" href="/showtimes">Lịch chiếu</a></li>
            <?php if ($user): ?><li><a class="nav-link <?= str_starts_with($path, '/my-bookings') ? 'active' : '' ?>" href="/my-bookings">Vé của tôi</a></li><?php endif; ?>
        </ul>
        <form class="nav-search order-last order-md-0" action="/movies" method="get" role="search">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="<?= e($path === '/movies' ? ($_GET['q'] ?? '') : '') ?>" maxlength="100"
                   placeholder="Tìm phim theo tên, ví dụ: Biệt đội siêu anh hùng" aria-label="Tìm phim">
        </form>
        <div class="nav-actions d-flex align-items-center gap-2 ms-auto ms-md-0">
            <?php if ($user): ?>
                <?php if ($user['role'] === 'admin'): ?><a class="btn btn-sm btn-detail" href="/admin/dashboard">Admin</a><?php endif; ?>
                <a class="small text-muted-2 text-decoration-none text-nowrap" href="/profile"><i class="bi bi-person-circle"></i> <span class="d-none d-sm-inline"><?= e($user['name']) ?></span></a>
                <form action="/logout" method="post" class="m-0"><?= csrfField() ?><button class="btn btn-sm btn-outline-light">Đăng xuất</button></form>
            <?php else: ?>
                <a class="btn btn-sm btn-buy px-3" href="/login">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
