<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public static function showLogin(): void
    {
        if (currentUser()) redirect('');
        render('auth/login', ['pageTitle' => 'Đăng nhập', 'old' => []]);
    }

    public static function login(): void
    {
        requireCsrf();
        $email = trim($_POST['email'] ?? '');
        if (loginThrottled()) {
            setFlash('danger', 'Đăng nhập sai quá nhiều lần. Thử lại sau 5 phút.');
            redirect('login');
        }
        $user = User::findByEmail($email);
        if (!$user || !password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])) {
            recordLoginFailure();
            setFlash('danger', 'Email hoặc mật khẩu không đúng.');
            redirect('login');
        }
        if ($user['status'] !== 'active') {
            setFlash('danger', 'Tài khoản đã bị khóa.');
            redirect('login');
        }
        regenerateSession();
        $_SESSION['user_id'] = (int)$user['id'];
        unset($_SESSION['login_fail']);
        $intended = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);
        if ($user['role'] === 'customer' && is_string($intended) && preg_match('#^/(?!/)#', $intended)) {
            header('Location: ' . $intended);
            exit;
        }
        redirectByRole($user);
    }

    public static function showRegister(): void
    {
        if (currentUser()) redirect('');
        render('auth/register', ['pageTitle' => 'Đăng ký', 'errors' => [], 'old' => []]);
    }

    public static function register(): void
    {
        requireCsrf();
        $errors = validateRegistration($_POST);
        if (!$errors && User::findByEmail(trim($_POST['email']))) {
            $errors['email'] = 'Email đã được sử dụng.';
        }
        if ($errors) {
            render('auth/register', ['pageTitle' => 'Đăng ký', 'errors' => $errors, 'old' => $_POST]);
            return;
        }
        User::create(trim($_POST['name']), trim($_POST['email']), $_POST['password']);
        setFlash('success', 'Đăng ký thành công. Hãy đăng nhập.');
        redirect('login');
    }

    public static function logout(): void
    {
        requireCsrf();
        $_SESSION = [];
        session_destroy();
        session_start();
        setFlash('success', 'Đã đăng xuất.');
        redirect('');
    }
}
