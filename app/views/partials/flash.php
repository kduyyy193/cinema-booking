<?php foreach (pullFlash() as $f): ?>
    <div class="container mt-3"><div class="alert alert-<?= e($f['type']) ?> mb-0"><?= e($f['message']) ?></div></div>
<?php endforeach; ?>
