<!doctype html>
<html lang="vi">
<head>
    <?php require BASE_PATH . '/app/views/partials/head.php'; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<?php require BASE_PATH . '/app/views/partials/site_nav.php'; ?>

<main>
    <?php require BASE_PATH . '/app/views/partials/flash.php'; ?>
    <?= $content ?>
</main>

<?php require BASE_PATH . '/app/views/partials/site_footer.php'; ?>
<?php require BASE_PATH . '/app/views/partials/confirm_modal.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(assetUrl('js/confirm.js')) ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
