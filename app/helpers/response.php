<?php
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pullFlash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function render(string $view, array $data = [], string $layout = 'main'): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require BASE_PATH . "/app/views/$view.php";
    $content = ob_get_clean();
    ob_start();
    require BASE_PATH . "/app/views/layouts/$layout.php";
    $html = ob_get_clean();
    applyPageCaching($html);
    echo $html;
}

function sendConditionalHeaders(string $etag, ?int $lastModified = null): void
{
    header('ETag: ' . $etag);
    if ($lastModified !== null) {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
    }
    $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
    $notModified = false;
    if ($ifNoneMatch !== null) {
        $strip = static fn(string $tag): string => preg_replace('/^W\//', '', trim($tag));
        $sent = array_map($strip, explode(',', $ifNoneMatch));
        $notModified = trim($ifNoneMatch) === '*' || in_array($strip($etag), $sent, true);
    } elseif ($lastModified !== null && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $since = strtotime((string)$_SERVER['HTTP_IF_MODIFIED_SINCE']);
        $notModified = $since !== false && $lastModified <= $since;
    }
    if ($notModified) {
        http_response_code(304);
        exit;
    }
}

function applyPageCaching(string $html): void
{
    $cacheable = ['/', '/movies', '/movie', '/showtimes', '/select-showtime'];
    $path = rtrim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true) && http_response_code() === 200 && in_array($path, $cacheable, true)) {
        header('Cache-Control: private, no-cache');
        sendConditionalHeaders('W/"' . md5($html) . '"');
        return;
    }
    header('Cache-Control: no-store');
}

function assetUrl(string $path): string
{
    $path = ltrim($path, '/');
    $file = BASE_PATH . '/public/assets/' . $path;
    return '/assets/' . $path . '?v=' . (is_file($file) ? filemtime($file) : 0);
}

function serveStaticFile(string $file): void
{
    $types = ['css' => 'text/css; charset=utf-8', 'js' => 'application/javascript; charset=utf-8', 'jpg' => 'image/jpeg',
              'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'svg' => 'image/svg+xml',
              'ico' => 'image/x-icon', 'woff2' => 'font/woff2', 'map' => 'application/json'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!isset($types[$ext])) {
        renderError(404, 'Không tìm thấy trang.');
        return;
    }
    $modified = (int)filemtime($file);
    $size = (int)filesize($file);
    header('Content-Type: ' . $types[$ext]);
    header('Cache-Control: public, max-age=604800');
    header('X-Content-Type-Options: nosniff');
    sendConditionalHeaders('"' . dechex($modified) . '-' . dechex($size) . '"', $modified);
    header('Content-Length: ' . $size);
    readfile($file);
}

function renderError(int $code, string $message = ''): void
{
    http_response_code($code);
    $titles = [403 => 'Không có quyền truy cập', 404 => 'Không tìm thấy trang', 500 => 'Lỗi hệ thống'];
    render('errors/error', ['code' => $code, 'title' => $titles[$code] ?? 'Lỗi', 'message' => $message]);
}

function jsonResponse(array $data, int $code = 200): never
{
    header('Cache-Control: no-store');
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function logApplicationError(string $msg): void
{
    @file_put_contents(config('log_path'), '[' . date('c') . "] $msg\n", FILE_APPEND);
}

function formatVnd(int $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

function paginate(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int)ceil($total / $perPage));
    $page  = min(max(1, $page), $pages);
    return ['page' => $page, 'pages' => $pages, 'per_page' => $perPage, 'offset' => ($page - 1) * $perPage, 'total' => $total];
}

function siteName(): string
{
    return \App\Models\Setting::get('site_name', config('app_name')) ?? (string)config('app_name');
}

function validDate(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

function intOrZero($value): int
{
    return is_scalar($value) && preg_match('/^\d+$/', (string)$value) ? (int)$value : 0;
}

function postedIds($value): array
{
    if (!is_array($value)) return [];
    $ids = [];
    foreach ($value as $v) {
        if (!is_scalar($v) || !preg_match('/^\d+$/', (string)$v)) return [];
        $ids[] = (int)$v;
    }
    return $ids;
}
