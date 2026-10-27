<!doctype html>
<html lang="vi">
<head>
    <?php require BASE_PATH . '/app/views/partials/head.php'; ?>
</head>
<body>
<?php require BASE_PATH . '/app/views/partials/site_nav.php'; ?>
<main>
    <?php require BASE_PATH . '/app/views/partials/flash.php'; ?>
    <?= $content ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
