<?php
declare(strict_types=1);

class AdminSettingsController
{
    private const KEYS = ['site_name', 'cancel_before_minutes', 'showtime_buffer_minutes', 'hold_minutes'];

    public static function index(): void
    {
        requireRole('admin');
        self::view(Setting::all(), []);
    }

    private static function view(array $values, array $errors): void
    {
        render('admin/settings', ['pageTitle' => 'Cài đặt hệ thống', 'values' => $values, 'errors' => $errors], 'admin');
    }

    public static function update(): void
    {
        requireRole('admin');
        requireCsrf();
        $errors = validateBookingPolicy($_POST);
        if ($errors) {
            self::view(array_intersect_key($_POST, array_flip(self::KEYS)), $errors);
            return;
        }
        withTransaction(function (): void {
            foreach (self::KEYS as $key) {
                Setting::set($key, trim((string)$_POST[$key]));
            }
        });
        setFlash('success', 'Đã lưu cài đặt.');
        redirect('admin/settings');
    }
}
