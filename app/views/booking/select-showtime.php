<div class="container py-4">
    <h3 class="mb-1">Chọn suất chiếu</h3>
    <p class="text-muted-2"><?= e($movie['title']) ?> · <?= (int)$movie['duration'] ?> phút · <?= e($movie['age_rating']) ?></p>

    <form method="get" action="/select-showtime" class="mb-3">
        <input type="hidden" name="movie" value="<?= (int)$movie['id'] ?>">
        <input type="hidden" name="date" value="<?= e($date) ?>">
        <label class="form-label">Rạp</label>
        <select class="form-select" name="cinema" onchange="this.form.submit()" style="max-width:360px">
            <?php foreach ($cinemas as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cinemaId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-detail mt-2">Chọn</button></noscript>
    </form>

    <div class="date-tabs mb-4">
        <?php foreach ($days as $d): ?>
            <a href="/select-showtime?movie=<?= (int)$movie['id'] ?>&amp;cinema=<?= $cinemaId ?>&amp;date=<?= e($d['date']) ?>" class="<?= $d['date'] === $date ? 'active' : '' ?>"><?= e($d['label']) ?><small><?= e($d['sub']) ?></small></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$showtimes): ?><p class="text-muted-2">Không có suất chiếu trong rạp và ngày đã chọn.</p><?php endif; ?>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($showtimes as $s): ?>
            <?php if ((int)$s['seats_left'] > 0): ?>
                <a class="btn btn-outline-light" href="/select-seats?showtime=<?= (int)$s['id'] ?>">
                    <?= e(date('H:i', strtotime($s['start_time']))) ?><br><small class="text-muted-2"><?= e($s['room_name']) ?> · <?= e(formatVnd((int)$s['base_price'])) ?></small>
                </a>
            <?php else: ?>
                <span class="btn btn-outline-secondary disabled"><?= e(date('H:i', strtotime($s['start_time']))) ?><br><small>Hết vé</small></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
