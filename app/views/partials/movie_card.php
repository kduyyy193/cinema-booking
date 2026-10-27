<?php
$bg = $m['poster'] ? "background-image:url('/uploads/" . e($m['poster']) . "')" : ''; ?>
<div class="movie-card">
    <div class="poster" style="<?= $bg ?>"><?= $m['poster'] ? '' : e($m['title']) ?></div>
    <div class="body">
        <div class="fw-semibold text-truncate"><?= e($m['title']) ?></div>
        <div class="meta"><span><i class="bi bi-clock"></i> <?= (int)$m['duration'] ?>'</span><span><i class="bi bi-person"></i> <?= e($m['age_rating']) ?></span></div>
        <div class="actions">
            <a class="btn btn-buy" href="/movie?id=<?= (int)$m['id'] ?>#showtimes">Mua vé</a>
            <a class="btn btn-detail" href="/movie?id=<?= (int)$m['id'] ?>">Chi tiết ›</a>
        </div>
    </div>
</div>
