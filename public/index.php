<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$root = dirname(__DIR__);
require $root . '/app/config/database.php';
date_default_timezone_set(config('timezone'));
require $root . '/vendor/autoload.php';

if (PHP_SAPI === 'cli-server' && preg_match('#^/(assets|uploads)/#', $path) && !preg_match('/\.(php|phtml|phar)$/i', $path)) {
    $real = realpath(__DIR__ . $path);
    if ($real !== false && is_file($real) && str_starts_with($real, realpath(__DIR__))) {
        serveStaticFile($real);
        return;
    }
}

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

if (!is_file(config('db_path'))) {
    http_response_code(500);
    exit('Database chưa được tạo. Chạy: php database/seed.php');
}

$method = $_SERVER['REQUEST_METHOD'] === 'HEAD' ? 'GET' : $_SERVER['REQUEST_METHOD'];
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
