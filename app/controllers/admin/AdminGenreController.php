<?php
declare(strict_types=1);

class AdminGenreController
{
    public static function index(): void
    {
        requireRole('admin');
        render('admin/genres/index', ['pageTitle' => 'Thể loại', 'genres' => Genre::all()], 'admin');
    }

    public static function store(): void
    {
        requireRole('admin');
        requireCsrf();
        $name = trim((string)($_POST['name'] ?? ''));
        $error = Genre::validateName($name);
        if ($error) {
            setFlash('danger', $error);
        } else {
            Genre::create($name);
            setFlash('success', 'Đã thêm thể loại.');
        }
        redirect('admin/genres');
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $id = intOrZero($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $error = Genre::validateName($name, $id);
        if ($error) {
            setFlash('danger', $error);
        } else {
            Genre::update($id, $name);
            setFlash('success', 'Đã cập nhật thể loại.');
        }
        redirect('admin/genres');
    }

    public static function delete(): void
    {
        requireRole('admin');
        requireCsrf();
        try {
            Genre::delete(intOrZero($_POST['id'] ?? 0));
            setFlash('success', 'Đã xóa thể loại.');
        } catch (InvalidArgumentException $ex) {
            setFlash('danger', $ex->getMessage());
        }
        redirect('admin/genres');
    }
}
