<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Cinema;

class AdminCinemaController
{
    public static function index(): void
    {
        requireRole('admin');
        $q = trim((string)($_GET['q'] ?? ''));
        render('admin/cinemas/index', ['pageTitle' => 'Quản lý rạp', 'cinemas' => Cinema::allIncludingInactive($q), 'q' => $q], 'admin');
    }

    public static function store(): void
    {
        requireRole('admin');
        requireCsrf();
        [$errors, $data] = Cinema::validate($_POST);
        if ($errors) {
            setFlash('danger', implode(' ', $errors));
        } else {
            Cinema::create($data);
            setFlash('success', 'Đã thêm rạp.');
        }
        redirect('admin/cinemas');
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $id = intOrZero($_POST['id'] ?? 0);
        if (!Cinema::find($id)) { renderError(404, 'Không tìm thấy rạp.'); return; }
        [$errors, $data] = Cinema::validate($_POST);
        if ($errors) {
            setFlash('danger', implode(' ', $errors));
        } else {
            Cinema::update($id, $data);
            setFlash('success', 'Đã cập nhật rạp.');
        }
        redirect('admin/cinemas');
    }

    public static function status(): void
    {
        requireRole('admin');
        requireCsrf();
        $cinema = Cinema::find(intOrZero($_POST['id'] ?? 0));
        if (!$cinema) { renderError(404, 'Không tìm thấy rạp.'); return; }
        $next = $cinema['status'] === 'active' ? 'inactive' : 'active';
        Cinema::setStatus((int)$cinema['id'], $next);
        setFlash('success', $next === 'active' ? 'Đã bật rạp.' : 'Đã tắt rạp: các suất chiếu của rạp không còn đặt được.');
        redirect('admin/cinemas');
    }
}
