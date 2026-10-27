<?php
declare(strict_types=1);

class ProfileController
{
    private static function view(array $user, array $data = []): void
    {
        $account = User::findById((int)$user['id']);
        render('profile/show', $data + [
            'pageTitle' => 'Hồ sơ cá nhân', 'account' => $account, 'old' => [],
            'profileErrors' => [], 'passwordErrors' => [],
        ]);
    }

    public static function show(): void
    {
        self::view(requireLogin());
    }

    public static function update(): void
    {
        $user = requireLogin();
        requireCsrf();
        [$errors, $data] = validateProfile($_POST);
        if ($errors) {
            self::view($user, ['profileErrors' => $errors, 'old' => $_POST]);
            return;
        }
        User::updateProfile((int)$user['id'], $data['name'], $data['phone']);
        setFlash('success', 'Đã cập nhật hồ sơ.');
        redirect('profile');
    }

    public static function password(): void
    {
        $user = requireLogin();
        requireCsrf();
        $account = User::findById((int)$user['id']);
        $errors = validateNewPassword((string)($_POST['password'] ?? ''), (string)($_POST['password_confirm'] ?? ''));
        if (!$account || !password_verify((string)($_POST['current_password'] ?? ''), $account['password_hash'])) {
            $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
        }
        if ($errors) {
            self::view($user, ['passwordErrors' => $errors]);
            return;
        }
        User::updatePassword((int)$user['id'], (string)$_POST['password']);
        regenerateSession();
        setFlash('success', 'Đã đổi mật khẩu.');
        redirect('profile');
    }
}
