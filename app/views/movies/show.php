<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-4"><div class="movie-card"><div class="poster" style="<?= $movie['poster'] ? "background-image:url('/uploads/" . e($movie['poster']) . "')" : '' ?>"><?= $movie['poster'] ? '' : e($movie['title']) ?></div></div></div>
        <div class="col-md-8">
            <h2><?= e($movie['title']) ?></h2>
            <p class="text-muted-2"><?= (int)$movie['duration'] ?> phút · <?= e($movie['age_rating']) ?> · Khởi chiếu <?= e($movie['release_date']) ?></p>
            <p><?php foreach ($genres as $g): ?><span class="badge bg-secondary me-1"><?= e($g['name']) ?></span><?php endforeach; ?></p>
            <p><?= nl2br(e($movie['description'])) ?></p>
            <p class="small"><strong>Đạo diễn:</strong> <?= e($movie['director']) ?><br><strong>Diễn viên:</strong> <?= e($movie['cast_list']) ?></p>
            <?php if ($movie['trailer_url']): ?>
                <a class="btn btn-detail" href="<?= e($movie['trailer_url']) ?>" target="_blank" rel="noopener"><i class="bi bi-play-circle me-1"></i>Xem trailer</a>
            <?php endif; ?>
            <a class="btn btn-buy ms-1" href="/select-showtime?movie=<?= (int)$movie['id'] ?>">Đặt vé</a>
        </div>
    </div>

    <h5 class="mt-5" id="showtimes">Suất chiếu</h5>
    <div class="date-tabs mb-3">
        <?php foreach ($days as $d): ?>
            <a href="/movie?id=<?= (int)$movie['id'] ?>&amp;date=<?= e($d['date']) ?>#showtimes" class="<?= $d['date'] === $date ? 'active' : '' ?>"><?= e($d['label']) ?><small><?= e($d['sub']) ?></small></a>
        <?php endforeach; ?>
    </div>
    <?php if (!$byCinema): ?><p class="text-muted-2">Chưa có suất chiếu trong ngày này.</p><?php endif; ?>
    <?php foreach ($byCinema as $cinemaName => $list): ?>
        <div class="panel mb-3">
            <h6 class="mb-3"><i class="bi bi-building me-1"></i><?= e($cinemaName) ?></h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($list as $s): ?>
                    <?php if ((int)$s['seats_left'] > 0): ?>
                        <a class="btn btn-outline-light btn-sm" href="/select-seats?showtime=<?= (int)$s['id'] ?>" title="<?= e($s['room_name']) ?> · <?= e(formatVnd((int)$s['base_price'])) ?>">
                            <?= e(date('H:i', strtotime($s['start_time']))) ?> <small class="text-muted-2">· <?= e($s['room_name']) ?> · còn <?= (int)$s['seats_left'] ?></small>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-outline-secondary btn-sm disabled"><?= e(date('H:i', strtotime($s['start_time']))) ?> · Hết vé</span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
