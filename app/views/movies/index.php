<div class="container py-4">
    <form class="row g-2 mb-4" method="get" action="/movies">
        <div class="col-md-5"><input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tên phim..."></div>
        <div class="col-md-3"><select class="form-select" name="genre" onchange="this.form.submit()"><option value="">Tất cả thể loại</option>
            <?php foreach ($genres as $g): ?><option value="<?= (int)$g['id'] ?>" <?= (int)($_GET['genre'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="status" onchange="this.form.submit()"><option value="">Mọi trạng thái</option>
            <option value="now_showing" <?= ($_GET['status'] ?? '') === 'now_showing' ? 'selected' : '' ?>>Đang chiếu</option>
            <option value="upcoming" <?= ($_GET['status'] ?? '') === 'upcoming' ? 'selected' : '' ?>>Sắp chiếu</option></select></div>
        <div class="col-md-2"><button class="btn btn-detail w-100">Lọc</button></div>
    </form>
    <?php if (!$movies): ?><p class="text-muted-2">Không tìm thấy phim phù hợp.</p><?php endif; ?>
    <div class="row g-3">
        <?php foreach ($movies as $m): ?><div class="col-6 col-md-3"><?php require BASE_PATH . '/app/views/partials/movie_card.php'; ?></div><?php endforeach; ?>
    </div>
    <?php $basePath = '/movies'; require BASE_PATH . '/app/views/partials/pagination.php'; ?>
</div>
