<?php
declare(strict_types=1);

function currentUser(): ?array
{
    static $cache = [];
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) return null;
    if (!isset($cache[$id])) {
        $st = getPDO()->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
        $st->execute([$id]);
        $cache[$id] = $st->fetch() ?: null;
    }
    $u = $cache[$id];
    if (!$u || $u['status'] !== 'active') {
        unset($_SESSION['user_id']);
        return null;
    }
    return $u;
}

function requireLogin(): array
{
    $u = currentUser();
    if (!$u) {
        setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/';
        redirect('login');
    }
    return $u;
}

function requireRole(string $role): array
{
    $u = requireLogin();
    if ($u['role'] !== $role) {
        renderError(403, 'Bạn không có quyền thực hiện thao tác này.');
        exit;
    }
    return $u;
}

function regenerateSession(): void
{
    session_regenerate_id(true);
}

function redirectByRole(array $user): never
{
    redirect($user['role'] === 'admin' ? 'admin/dashboard' : '');
}
