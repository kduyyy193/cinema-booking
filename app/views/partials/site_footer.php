<?php
$footerUser = currentUser();
$footerCinemas = \App\Models\Cinema::all();
?>
<footer class="site-footer no-print">
    <div class="container">
        <div class="row g-4 footer-main">
            <div class="col-lg-4">
                <a class="footer-brand" href="/"><i class="bi bi-film text-danger"></i> <?= e(siteName()) ?></a>
                <p class="footer-about">Đặt vé xem phim trực tuyến nhanh chóng: chọn suất chiếu, chọn ghế trực quan và thanh toán chỉ trong vài bước.</p>
                <ul class="footer-contact list-unstyled">
                    <li><i class="bi bi-envelope"></i> demo@cinema.test</li>
                    <li><i class="bi bi-clock"></i> Hỗ trợ 08:00 – 22:00 mỗi ngày</li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Khám phá</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="/">Trang chủ</a></li>
                    <li><a href="/movies?status=now_showing">Phim đang chiếu</a></li>
                    <li><a href="/movies?status=upcoming">Phim sắp chiếu</a></li>
                    <li><a href="/showtimes">Lịch chiếu</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Tài khoản</h6>
                <ul class="footer-links list-unstyled">
                    <?php if ($footerUser): ?>
                        <li><a href="/my-bookings">Vé của tôi</a></li>
                        <li><a href="/profile">Hồ sơ cá nhân</a></li>
                    <?php else: ?>
                        <li><a href="/login">Đăng nhập</a></li>
                        <li><a href="/register">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6>Hệ thống rạp</h6>
                <ul class="footer-cinemas list-unstyled">
                    <?php foreach ($footerCinemas as $c): ?>
                        <li>
                            <strong><?= e($c['name']) ?></strong>
                            <?php if ($c['address']): ?><span><i class="bi bi-geo-alt"></i> <?= e($c['address']) ?></span><?php endif; ?>
                            <?php if ($c['phone']): ?><span><i class="bi bi-telephone"></i> <?= e($c['phone']) ?></span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!$footerCinemas): ?><li class="text-muted-2">Đang cập nhật.</li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <?= e(siteName()) ?>. Bảo lưu mọi quyền.</span>
            <span>Dự án học phần Công nghệ Web (CT275) · Thanh toán chỉ mang tính giả lập</span>
        </div>
    </div>
</footer>
