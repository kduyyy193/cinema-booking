<?php
declare(strict_types=1);

function validateRegistration(array $in): array
{
    $errors = [];
    $name  = trim($in['name'] ?? '');
    $email = trim($in['email'] ?? '');
    $pass  = (string)($in['password'] ?? '');
    if ($name === '' || mb_strlen($name) > 100)             $errors['name'] = 'Họ tên không hợp lệ.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors['email'] = 'Email không hợp lệ.';
    if (strlen($pass) < 8)                                  $errors['password'] = 'Mật khẩu tối thiểu 8 ký tự.';
    if ($pass !== (string)($in['password_confirm'] ?? ''))  $errors['password_confirm'] = 'Xác nhận mật khẩu không khớp.';
    return $errors;
}

function loginThrottled(): bool
{
    $t = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
    return $t['until'] > time();
}

function recordLoginFailure(): void
{
    $t = $_SESSION['login_fail'] ?? ['n' => 0, 'until' => 0];
    $t['n']++;
    if ($t['n'] >= 5) { $t = ['n' => 0, 'until' => time() + 300]; }
    $_SESSION['login_fail'] = $t;
}

function validatePhone(string $phone): bool
{
    return $phone === '' || (preg_match('/^[0-9 +]+$/', $phone) === 1 && strlen($phone) >= 8 && strlen($phone) <= 15);
}

function validateProfile(array $in): array
{
    $errors = [];
    $name = trim((string)($in['name'] ?? ''));
    $phone = trim((string)($in['phone'] ?? ''));
    if ($name === '' || mb_strlen($name) > 100) $errors['name'] = 'Họ tên bắt buộc, tối đa 100 ký tự.';
    if (!validatePhone($phone)) $errors['phone'] = 'Số điện thoại gồm số, dấu cách, dấu +, dài 8–15 ký tự.';
    return [$errors, ['name' => $name, 'phone' => $phone === '' ? null : $phone]];
}

function validateNewPassword(string $password, string $confirm): array
{
    $errors = [];
    if (strlen($password) < 8) $errors['password'] = 'Mật khẩu mới tối thiểu 8 ký tự.';
    if ($password !== $confirm) $errors['password_confirm'] = 'Xác nhận mật khẩu không khớp.';
    return $errors;
}

function validateMovieData(array $in): array
{
    $errors = [];
    $title = trim((string)($in['title'] ?? ''));
    $duration = trim((string)($in['duration'] ?? ''));
    $release = trim((string)($in['release_date'] ?? ''));
    $age = (string)($in['age_rating'] ?? '');
    $status = (string)($in['status'] ?? '');
    $trailer = trim((string)($in['trailer_url'] ?? ''));
    $genreIds = postedIds($in['genre_ids'] ?? []);

    if ($title === '' || mb_strlen($title) > 200) $errors['title'] = 'Tên phim bắt buộc, tối đa 200 ký tự.';
    if (!preg_match('/^\d+$/', $duration) || (int)$duration < 1 || (int)$duration > 600) $errors['duration'] = 'Thời lượng từ 1 đến 600 phút.';
    if (!validDate($release)) $errors['release_date'] = 'Ngày khởi chiếu không hợp lệ (Y-m-d).';
    if (!in_array($age, ageRatings(), true)) $errors['age_rating'] = 'Phân loại không hợp lệ.';
    if (!in_array($status, ['now_showing', 'upcoming', 'hidden'], true)) $errors['status'] = 'Trạng thái không hợp lệ.';
    if ($trailer !== '') {
        $scheme = strtolower((string)parse_url($trailer, PHP_URL_SCHEME));
        if (!filter_var($trailer, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
            $errors['trailer_url'] = 'Trailer phải là URL http hoặc https.';
        }
    }
    if (!$genreIds) {
        $errors['genre_ids'] = 'Chọn ít nhất một thể loại.';
    } elseif (count(Genre::existingIds($genreIds)) !== count(array_unique($genreIds))) {
        $errors['genre_ids'] = 'Có thể loại không tồn tại.';
    }

    return [$errors, [
        'title' => $title,
        'description' => trim((string)($in['description'] ?? '')),
        'trailer_url' => $trailer === '' ? null : $trailer,
        'director' => trim((string)($in['director'] ?? '')),
        'cast_list' => trim((string)($in['cast_list'] ?? '')),
        'age_rating' => $age,
        'duration' => (int)$duration,
        'release_date' => $release,
        'status' => $status,
        'genre_ids' => array_values(array_unique($genreIds)),
    ]];
}

function validateBookingPolicy(array $input): array
{
    $errors = [];
    $siteName = trim((string)($input['site_name'] ?? ''));
    if ($siteName === '' || mb_strlen($siteName) > 60) $errors['site_name'] = 'Tên website bắt buộc, tối đa 60 ký tự.';
    $ranges = ['cancel_before_minutes' => [0, 10080], 'showtime_buffer_minutes' => [0, 120], 'hold_minutes' => [1, 60]];
    foreach ($ranges as $key => [$min, $max]) {
        $v = trim((string)($input[$key] ?? ''));
        if (!preg_match('/^\d+$/', $v) || (int)$v < $min || (int)$v > $max) {
            $errors[$key] = "Giá trị phải là số nguyên từ $min đến $max.";
        }
    }
    return $errors;
}
