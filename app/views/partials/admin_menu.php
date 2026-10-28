<div class="brand"><i class="bi bi-film"></i> <?= e(siteName()) ?></div>
<?php foreach ($nav as $href => [$icon, $label]): ?>
    <a href="<?= e($href) ?>" class="<?= str_starts_with($path, $href) ? 'active' : '' ?>"><i class="bi <?= e($icon) ?> me-2"></i><?= e($label) ?></a>
<?php endforeach; ?>
<div class="mt-auto pt-3 border-top border-secondary">
    <div class="small text-muted-2 mb-2"><?= e($user['name'] ?? '') ?></div>
    <a href="/" class="d-block mb-1"><i class="bi bi-box-arrow-up-right me-2"></i>Xem website</a>
    <form action="/logout" method="post"><?= csrfField() ?><button class="btn btn-sm btn-outline-info w-100">Đăng xuất</button></form>
</div>
