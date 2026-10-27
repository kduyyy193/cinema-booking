<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file)) {
        if (!str_starts_with($path, '/uploads/') || !preg_match('/\.(php|phtml|phar)$/i', $path)) return false;
    }
}

$root = dirname(__DIR__);
require $root . '/app/config/database.php';
date_default_timezone_set(config('timezone'));
foreach (['response', 'csrf', 'auth', 'labels', 'validation', 'payment', 'upload'] as $h) require "$root/app/helpers/$h.php";
spl_autoload_register(function (string $class) use ($root): void {
    foreach (['models', 'controllers', 'controllers/admin'] as $dir) {
        $file = "$root/app/$dir/$class.php";
        if (is_file($file)) { require $file; return; }
    }
});

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

if (!is_file(config('db_path'))) {
    http_response_code(500);
    exit('Database chưa được tạo. Chạy: php database/seed.php');
}

$method = $_SERVER['REQUEST_METHOD'];
$route  = rtrim($path, '/') ?: '/';
try {
    foreach (require $root . '/routes/web.php' as [$m, $p, $handler]) {
        if ($m === $method && $p === $route) {
            $handler();
            return;
        }
    }
    renderError(404, 'Không tìm thấy trang.');
} catch (Throwable $ex) {
    logApplicationError($ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    renderError(500, 'Đã xảy ra lỗi. Vui lòng thử lại.');
}
