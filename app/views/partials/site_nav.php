<?php $user = currentUser(); $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<nav class="navbar navbar-expand-md site-nav py-2" data-bs-theme="dark">
    <div class="container flex-wrap gap-2">
        <a class="navbar-brand brand me-md-3" href="/"><i class="bi bi-film text-danger"></i> <?= e(siteName()) ?></a>
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse gap-2 gap-md-3" id="main-nav">
            <ul class="navbar-nav me-md-auto">
                <li class="nav-item"><a class="nav-link <?= $path === '/' ? 'active' : '' ?>" href="/">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link <?= str_starts_with($path, '/movie') ? 'active' : '' ?>" href="/movies">Phim</a></li>
                <li class="nav-item"><a class="nav-link <?= str_starts_with($path, '/showtimes') ? 'active' : '' ?>" href="/showtimes">Lịch chiếu</a></li>
                <?php if ($user): ?><li class="nav-item"><a class="nav-link <?= str_starts_with($path, '/my-bookings') ? 'active' : '' ?>" href="/my-bookings">Vé của tôi</a></li><?php endif; ?>
            </ul>
            <form class="nav-search" action="/movies" method="get" role="search">
                <i class="bi bi-search"></i>
                <input type="search" name="q" value="<?= e($path === '/movies' ? ($_GET['q'] ?? '') : '') ?>" maxlength="100"
                       placeholder="Tìm phim theo tên, ví dụ: Biệt đội siêu anh hùng" aria-label="Tìm phim">
            </form>
            <div class="nav-actions d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= e($user['name']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item" href="/my-bookings"><i class="bi bi-ticket-perforated me-2"></i>Vé của tôi</a></li>
                            <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Hồ sơ cá nhân</a></li>
                            <?php if ($user['role'] === 'admin'): ?><li><a class="dropdown-item" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i>Trang quản trị</a></li><?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="/logout" method="post" class="m-0"><?= csrfField() ?><button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button></form></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-light" href="/register">Đăng ký</a>
                    <a class="btn btn-sm btn-buy px-3" href="/login">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
