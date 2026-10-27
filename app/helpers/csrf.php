<?php
declare(strict_types=1);

function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrfToken()) . '">';
}

function verifyCsrfToken(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function requireCsrf(): void
{
    if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        renderError(403, 'Phiên làm việc không hợp lệ (CSRF). Vui lòng tải lại trang.');
        exit;
    }
}
