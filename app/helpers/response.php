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
    require BASE_PATH . "/app/views/layouts/$layout.php";
}

function renderError(int $code, string $message = ''): void
{
    http_response_code($code);
    $titles = [403 => 'Không có quyền truy cập', 404 => 'Không tìm thấy trang', 500 => 'Lỗi hệ thống'];
    render('errors/error', ['code' => $code, 'title' => $titles[$code] ?? 'Lỗi', 'message' => $message]);
}

function jsonResponse(array $data, int $code = 200): never
{
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
    return Setting::get('site_name', config('app_name')) ?? (string)config('app_name');
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
