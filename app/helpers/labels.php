<?php
declare(strict_types=1);

function bookingStatusLabel(string $status): string
{
    return [
        'pending'   => 'Chờ thanh toán',
        'confirmed' => 'Đã xác nhận',
        'cancelled' => 'Đã hủy',
        'expired'   => 'Hết hạn',
    ][$status] ?? $status;
}

function paymentStatusLabel(string $status): string
{
    return [
        'pending'    => 'Chờ thanh toán',
        'processing' => 'Đang xử lý',
        'success'    => 'Thành công',
        'failed'     => 'Thất bại',
        'expired'    => 'Hết hạn',
        'refunded'   => 'Đã hoàn tiền',
    ][$status] ?? $status;
}

function statusBadgeClass(string $status): string
{
    return [
        'pending'    => 'warning',
        'confirmed'  => 'success',
        'cancelled'  => 'secondary',
        'expired'    => 'dark',
        'processing' => 'info',
        'success'    => 'success',
        'failed'     => 'danger',
        'refunded'   => 'secondary',
    ][$status] ?? 'secondary';
}

function formatDateTime(?string $value, string $format = 'd/m/Y H:i'): string
{
    return $value ? date($format, strtotime($value)) : '';
}

function ageRatings(): array
{
    return ['P', 'K', 'T13', 'T16', 'T18', 'C'];
}

function nextDays(int $count = 7): array
{
    $names = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
    $days = [];
    for ($i = 0; $i < $count; $i++) {
        $t = strtotime("+$i day");
        $days[] = ['date' => date('Y-m-d', $t), 'label' => $i === 0 ? 'Hôm nay' : $names[(int)date('w', $t)], 'sub' => date('d/m', $t)];
    }
    return $days;
}

function pickDay(array $days, string $requested): string
{
    return in_array($requested, array_column($days, 'date'), true) ? $requested : $days[0]['date'];
}
