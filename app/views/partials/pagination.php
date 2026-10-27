<?php if (($pg['pages'] ?? 1) > 1): $base = $basePath ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<nav class="mt-4"><ul class="pagination justify-content-center flex-wrap">
    <?php for ($i = 1; $i <= $pg['pages']; $i++): $qs = http_build_query(array_merge($_GET, ['page' => $i])); ?>
        <li class="page-item <?= $i === $pg['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= e($base . '?' . $qs) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
