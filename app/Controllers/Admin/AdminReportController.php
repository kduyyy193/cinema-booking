<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Report;

class AdminReportController
{
    private static function range(): array
    {
        $from = trim((string)($_GET['from'] ?? ''));
        $to = trim((string)($_GET['to'] ?? ''));
        $errors = [];
        if ($from === '' && $to === '') {
            $to = date('Y-m-d');
            $from = date('Y-m-d', strtotime('-29 day'));
        }
        if (!validDate($from) || !validDate($to)) {
            $errors[] = 'Khoảng ngày không hợp lệ.';
            $to = date('Y-m-d');
            $from = date('Y-m-d', strtotime('-29 day'));
        } elseif ($from > $to) {
            $errors[] = 'Ngày bắt đầu không được lớn hơn ngày kết thúc.';
        } elseif ((strtotime($to) - strtotime($from)) / 86400 > 365) {
            $errors[] = 'Khoảng ngày tối đa 366 ngày.';
        }
        return [$from, $to, $errors];
    }

    public static function index(): void
    {
        requireRole('admin');
        [$from, $to, $errors] = self::range();
        $data = ['byDate' => [], 'byMovie' => [], 'byCinema' => [], 'summary' => ['revenue' => 0, 'orders' => 0, 'tickets' => 0]];
        if (!$errors) {
            $data = [
                'byDate' => Report::revenueByDate($from, $to), 'byMovie' => Report::revenueByMovie($from, $to),
                'byCinema' => Report::revenueByCinema($from, $to), 'summary' => Report::summary($from, $to),
            ];
        }
        render('admin/reports/index', [
            'pageTitle' => 'Báo cáo doanh thu', 'from' => $from, 'to' => $to, 'errors' => $errors,
            'scripts' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script><script src="' . assetUrl('js/dashboard.js') . '"></script>',
        ] + $data, 'admin');
    }

    public static function export(): void
    {
        requireRole('admin');
        [$from, $to, $errors] = self::range();
        if ($errors) {
            setFlash('danger', implode(' ', $errors));
            redirect('admin/reports');
        }
        $type = (string)($_GET['type'] ?? 'date');
        if ($type === 'movie') {
            $header = ['Phim', 'Số đơn', 'Số vé', 'Doanh thu (VND)'];
            $rows = array_map(fn($r) => [$r['name'], (int)$r['orders'], (int)$r['tickets'], (int)$r['revenue']], Report::revenueByMovie($from, $to));
        } elseif ($type === 'cinema') {
            $header = ['Rạp', 'Số đơn', 'Số vé', 'Doanh thu (VND)'];
            $rows = array_map(fn($r) => [$r['name'], (int)$r['orders'], (int)$r['tickets'], (int)$r['revenue']], Report::revenueByCinema($from, $to));
        } else {
            $type = 'date';
            $header = ['Ngày', 'Số đơn', 'Doanh thu (VND)'];
            $rows = array_map(fn($r) => [$r['day'], (int)$r['orders'], (int)$r['revenue']], Report::revenueByDate($from, $to));
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="doanh-thu-' . $type . '-' . $from . '_' . $to . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array_map([self::class, 'csvSafe'], $header), ',', '"', '\\');
        foreach ($rows as $row) fputcsv($out, array_map([self::class, 'csvSafe'], $row), ',', '"', '\\');
        fclose($out);
        exit;
    }

    public static function csvSafe($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r", "\n"], true)) {
            return "'" . $value;
        }
        return $value;
    }
}
