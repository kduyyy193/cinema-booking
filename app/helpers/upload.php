<?php
declare(strict_types=1);

function handlePosterUpload(array $file): string
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('Ảnh poster vượt quá 2 MB.');
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Tải ảnh poster thất bại.');
    }
    $tmp = (string)$file['tmp_name'];
    if (!is_uploaded_file($tmp)) {
        throw new RuntimeException('Tệp tải lên không hợp lệ.');
    }
    if ((int)$file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Ảnh poster vượt quá 2 MB.');
    }

    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string)finfo_file($finfo, $tmp) : '';
    if (!isset($extensions[$mime]) || @getimagesize($tmp) === false) {
        throw new RuntimeException('Poster phải là ảnh JPG, PNG hoặc WEBP hợp lệ.');
    }

    $dir = BASE_PATH . '/public/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        throw new RuntimeException('Không tạo được thư mục lưu ảnh.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        throw new RuntimeException('Không lưu được ảnh poster.');
    }
    return $name;
}

function deletePosterFile(?string $poster): void
{
    if (!$poster) return;
    $path = BASE_PATH . '/public/uploads/' . basename($poster);
    if (is_file($path)) @unlink($path);
}
