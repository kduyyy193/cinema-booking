<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Booking;
use App\Models\Report;

class AdminDashboardController
{
    public static function index(): void
    {
        requireRole('admin');
        Booking::expireStale();
        render('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'totals' => Report::totals(),
            'popular' => Report::popularMovies(5),
            'recent' => Report::recentBookings(5),
            'upcoming' => Report::upcomingShowtimes(5),
            'chart' => Report::revenueLastDays(7),
            'scripts' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script><script src="' . assetUrl('js/dashboard.js') . '"></script>',
        ], 'admin');
    }
}
