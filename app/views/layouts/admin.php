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
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="admin-body text-light">
<div class="admin-shell">
    <aside class="admin-side">
        <div class="brand"><i class="bi bi-film"></i> <?= e(siteName()) ?></div>
        <?php foreach ($nav as $href => [$icon, $label]): ?>
            <a href="<?= e($href) ?>" class="<?= str_starts_with($path, $href) ? 'active' : '' ?>"><i class="bi <?= e($icon) ?> me-2"></i><?= e($label) ?></a>
        <?php endforeach; ?>
        <div class="mt-auto pt-3 border-top border-secondary">
            <div class="small text-muted-2 mb-2"><?= e($user['name'] ?? '') ?></div>
            <a href="/" class="d-block mb-1"><i class="bi bi-box-arrow-up-right me-2"></i>Xem website</a>
            <form action="/logout" method="post"><?= csrfField() ?><button class="btn btn-sm btn-outline-info w-100">Đăng xuất</button></form>
        </div>
    </aside>
    <div class="admin-main">
        <?php foreach (pullFlash() as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
        <?= $content ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
