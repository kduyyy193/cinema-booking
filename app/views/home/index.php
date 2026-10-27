<section class="hero">
    <div class="container">
        <?php if ($featured): ?>
            <h1 class="display-6">Xem phim yêu thích trên màn ảnh lớn</h1>
            <h5 class="mt-3"><?= e($featured['title']) ?></h5>
            <p><?= e(mb_strimwidth((string)$featured['description'], 0, 200, '…')) ?></p>
            <a href="/movie?id=<?= (int)$featured['id'] ?>#showtimes" class="btn btn-buy px-4 py-2">Mua vé</a>
            <?php if ($featured['trailer_url']): ?><a href="<?= e($featured['trailer_url']) ?>" target="_blank" rel="noopener" class="btn btn-link text-white text-decoration-none ms-2"><i class="bi bi-play-circle fs-4 align-middle"></i> Xem trailer</a><?php endif; ?>
        <?php else: ?>
            <h1 class="display-6">Xem phim yêu thích trên màn ảnh lớn</h1>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <div class="date-tabs mb-4">
        <?php foreach ($days as $d): ?>
            <a href="/?date=<?= e($d['date']) ?>" class="<?= $d['date'] === $date ? 'active' : '' ?>"><?= e($d['label']) ?><small><?= e($d['sub']) ?></small></a>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between mb-2"><span class="section-title">Phim đang chiếu</span><a class="small text-muted-2 text-decoration-none" href="/movies?status=now_showing">Xem thêm ›</a></div>
    <?php if (!$nowShowing): ?><p class="text-muted-2">Chưa có suất chiếu trong ngày này.</p><?php endif; ?>
    <div class="row g-3 mb-4">
        <?php foreach ($nowShowing as $m): ?><div class="col-6 col-md-3"><?php require BASE_PATH . '/app/views/partials/movie_card.php'; ?></div><?php endforeach; ?>
    </div>

    <?php if ($soon): ?>
    <div class="d-flex justify-content-between mb-2"><span class="section-title">Suất chiếu sắp tới</span><a class="small text-muted-2 text-decoration-none" href="/showtimes">Xem lịch chiếu ›</a></div>
    <div class="panel p-0 mb-4">
        <?php foreach ($soon as $s): ?>
            <div class="d-flex flex-wrap align-items-center gap-3 px-3 py-2 border-bottom border-secondary-subtle">
                <span class="fw-semibold flex-grow-1"><?= e($s['movie_title']) ?></span>
                <span class="text-muted-2 small"><?= e($s['cinema_name']) ?> – <?= e($s['room_name']) ?></span>
                <span class="small"><?= e(formatDateTime($s['start_time'], 'd/m H:i')) ?></span>
                <?php if ((int)$s['seats_left'] > 0): ?>
                    <a class="btn btn-sm btn-buy" href="/select-seats?showtime=<?= (int)$s['id'] ?>">Chọn ghế</a>
                <?php else: ?>
                    <span class="badge bg-secondary">Hết vé</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($upcoming): ?>
    <div class="d-flex justify-content-between mb-2"><span class="section-title">Sắp chiếu</span><a class="small text-muted-2 text-decoration-none" href="/movies?status=upcoming">Xem thêm ›</a></div>
    <div class="row g-3 mb-4">
        <?php foreach ($upcoming as $m): ?><div class="col-6 col-md-3"><?php require BASE_PATH . '/app/views/partials/movie_card.php'; ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-2"><span class="section-title">Thể loại</span></div>
    <div class="row g-3">
        <?php foreach ($genres as $g): ?>
            <div class="col-6 col-md-4"><a class="genre-tile" href="/movies?genre=<?= (int)$g['id'] ?>">
                <span class="icon"><i class="bi bi-camera-reels"></i></span>
                <span><strong><?= e($g['name']) ?></strong><br><small class="text-muted-2"><?= (int)$g['n'] ?> phim</small></span>
            </a></div>
        <?php endforeach; ?>
    </div>
</div>
