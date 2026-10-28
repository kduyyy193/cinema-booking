<?php
$user = currentUser();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$nav = [
    '/admin/dashboard' => ['bi-speedometer2', 'Dashboard'],
    '/admin/movies'    => ['bi-film', 'Phim'],
    '/admin/genres'    => ['bi-tags', 'Thể loại'],
    '/admin/cinemas'   => ['bi-building', 'Rạp'],
    '/admin/rooms'     => ['bi-door-open', 'Phòng chiếu'],
    '/admin/seats'     => ['bi-grid-3x3-gap', 'Ghế'],
    '/admin/showtimes' => ['bi-calendar-event', 'Suất chiếu'],
    '/admin/bookings'  => ['bi-ticket-perforated', 'Đơn vé'],
    '/admin/users'     => ['bi-people', 'Người dùng'],
    '/admin/reports'   => ['bi-graph-up', 'Báo cáo'],
    '/admin/settings'  => ['bi-gear', 'Cài đặt'],
];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? "") . " · Admin · " . siteName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(assetUrl('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body text-light">
<div class="admin-shell">
    <aside class="admin-side d-none d-lg-flex">
        <?php require BASE_PATH . '/app/views/partials/admin_menu.php'; ?>
    </aside>
    <div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="admin-menu" aria-labelledby="admin-menu-title">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="admin-menu-title">Menu quản trị</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>
        <div class="offcanvas-body admin-side">
            <?php require BASE_PATH . '/app/views/partials/admin_menu.php'; ?>
        </div>
    </div>
    <div class="admin-main">
        <div class="admin-topbar d-lg-none mb-3">
            <button class="btn btn-outline-info btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-menu" aria-controls="admin-menu"><i class="bi bi-list"></i> Menu</button>
            <span class="fw-semibold"><?= e(siteName()) ?></span>
        </div>
        <?php foreach (pullFlash() as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
        <?= $content ?>
    </div>
</div>
<?php require BASE_PATH . '/app/views/partials/confirm_modal.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(assetUrl('js/confirm.js')) ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
