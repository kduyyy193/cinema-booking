<div class="container py-5 text-center">
    <h1 class="display-1 fw-bold text-danger"><?= (int)$code ?></h1>
    <h4><?= e($title) ?></h4>
    <?php if ($message): ?><p class="text-muted-2"><?= e($message) ?></p><?php endif; ?>
    <a href="/" class="btn btn-buy mt-3">Về trang chủ</a>
</div>
