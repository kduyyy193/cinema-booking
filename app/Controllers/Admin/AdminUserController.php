<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;

class AdminUserController
{
    public static function index(): void
    {
        $admin = requireRole('admin');
        $filters = ['q' => (string)($_GET['q'] ?? ''), 'role' => (string)($_GET['role'] ?? ''), 'status' => (string)($_GET['status'] ?? '')];
        [$rows, $pg] = User::adminList($filters, max(1, intOrZero($_GET['page'] ?? 1)));
        render('admin/users/index', [
            'pageTitle' => 'Người dùng', 'rows' => $rows, 'pg' => $pg, 'filters' => $filters, 'selfId' => (int)$admin['id'],
        ], 'admin');
    }

    private static function target(): array
    {
        $user = User::findById(intOrZero($_POST['id'] ?? 0));
        if (!$user) { renderError(404, 'Không tìm thấy người dùng.'); exit; }
        return $user;
    }

    public static function status(): void
    {
        $admin = requireRole('admin');
        requireCsrf();
        $user = self::target();
        $next = $user['status'] === 'active' ? 'locked' : 'active';

        if ($next === 'locked' && (int)$user['id'] === (int)$admin['id']) {
            setFlash('danger', 'Bạn không thể khóa chính mình.');
        } elseif ($next === 'locked' && $user['role'] === 'admin' && User::countActiveAdmins() <= 1) {
            setFlash('danger', 'Không thể khóa quản trị viên đang hoạt động cuối cùng.');
        } else {
            User::setStatus((int)$user['id'], $next);
            setFlash('success', $next === 'locked' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
        }
        redirect('admin/users');
    }

    public static function role(): void
    {
        $admin = requireRole('admin');
        requireCsrf();
        $user = self::target();
        $role = (string)($_POST['role'] ?? '');

        if (!in_array($role, ['customer', 'admin'], true)) {
            setFlash('danger', 'Vai trò không hợp lệ.');
        } elseif ((int)$user['id'] === (int)$admin['id']) {
            setFlash('danger', 'Bạn không thể đổi vai trò của chính mình.');
        } elseif ($role === 'customer' && $user['role'] === 'admin' && $user['status'] === 'active' && User::countActiveAdmins() <= 1) {
            setFlash('danger', 'Không thể hạ quyền quản trị viên cuối cùng.');
        } else {
            User::setRole((int)$user['id'], $role);
            setFlash('success', 'Đã cập nhật vai trò.');
        }
        redirect('admin/users');
    }
}
