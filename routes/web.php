<?php
declare(strict_types=1);

use App\Controllers\Admin\AdminBookingController;
use App\Controllers\Admin\AdminCinemaController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminGenreController;
use App\Controllers\Admin\AdminMovieController;
use App\Controllers\Admin\AdminReportController;
use App\Controllers\Admin\AdminRoomController;
use App\Controllers\Admin\AdminSeatController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\AdminShowtimeController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\MovieController;
use App\Controllers\PaymentController;
use App\Controllers\ProfileController;
use App\Controllers\ShowtimeController;
use App\Controllers\TicketController;
use App\Controllers\WebhookController;

return [
    ['GET', '/', [MovieController::class, 'home']],
    ['GET', '/movies', [MovieController::class, 'index']],
    ['GET', '/movie', [MovieController::class, 'show']],
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['GET', '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    ['GET', '/admin', fn() => redirect('admin/dashboard')],

    ['GET', '/showtimes', [ShowtimeController::class, 'index']],
    ['GET', '/select-showtime', [ShowtimeController::class, 'select']],
    ['GET', '/select-seats', [BookingController::class, 'seats']],
    ['GET', '/checkout', [BookingController::class, 'checkout']],
    ['POST', '/checkout', [BookingController::class, 'store']],
    ['GET', '/booking-success', [TicketController::class, 'success']],
    ['GET', '/my-bookings', [TicketController::class, 'mine']],
    ['GET', '/booking-detail', [TicketController::class, 'detail']],
    ['POST', '/booking/cancel', [BookingController::class, 'cancel']],

    ['GET', '/payment', [PaymentController::class, 'show']],
    ['POST', '/payment/create', [PaymentController::class, 'create']],
    ['POST', '/payment/process-demo', [PaymentController::class, 'processDemo']],
    ['POST', '/payment/simulate-qr', [PaymentController::class, 'simulateQr']],
    ['GET', '/payment/status', [PaymentController::class, 'status']],
    ['GET', '/payment/stream', [PaymentController::class, 'stream']],
    ['GET', '/payment/success', [PaymentController::class, 'success']],
    ['POST', '/webhooks/mock-payment', [WebhookController::class, 'mockPayment']],

    ['GET', '/profile', [ProfileController::class, 'show']],
    ['POST', '/profile', [ProfileController::class, 'update']],
    ['POST', '/profile/password', [ProfileController::class, 'password']],

    ['GET', '/admin/dashboard', [AdminDashboardController::class, 'index']],
    ['GET', '/admin/movies', [AdminMovieController::class, 'index']],
    ['GET', '/admin/movies/create', [AdminMovieController::class, 'create']],
    ['POST', '/admin/movies/store', [AdminMovieController::class, 'store']],
    ['GET', '/admin/movies/edit', [AdminMovieController::class, 'edit']],
    ['POST', '/admin/movies/update', [AdminMovieController::class, 'update']],
    ['POST', '/admin/movies/status', [AdminMovieController::class, 'status']],
    ['POST', '/admin/movies/delete', [AdminMovieController::class, 'delete']],
    ['GET', '/admin/genres', [AdminGenreController::class, 'index']],
    ['POST', '/admin/genres/store', [AdminGenreController::class, 'store']],
    ['POST', '/admin/genres/update', [AdminGenreController::class, 'update']],
    ['POST', '/admin/genres/delete', [AdminGenreController::class, 'delete']],
    ['GET', '/admin/cinemas', [AdminCinemaController::class, 'index']],
    ['POST', '/admin/cinemas/store', [AdminCinemaController::class, 'store']],
    ['POST', '/admin/cinemas/update', [AdminCinemaController::class, 'update']],
    ['POST', '/admin/cinemas/status', [AdminCinemaController::class, 'status']],
    ['GET', '/admin/rooms', [AdminRoomController::class, 'index']],
    ['POST', '/admin/rooms/store', [AdminRoomController::class, 'store']],
    ['POST', '/admin/rooms/update', [AdminRoomController::class, 'update']],
    ['GET', '/admin/seats', [AdminSeatController::class, 'index']],
    ['POST', '/admin/seats/update', [AdminSeatController::class, 'update']],
    ['POST', '/admin/seats/lock', [AdminSeatController::class, 'lock']],
    ['GET', '/admin/showtimes', [AdminShowtimeController::class, 'index']],
    ['GET', '/admin/showtimes/create', [AdminShowtimeController::class, 'create']],
    ['POST', '/admin/showtimes/store', [AdminShowtimeController::class, 'store']],
    ['GET', '/admin/showtimes/edit', [AdminShowtimeController::class, 'edit']],
    ['POST', '/admin/showtimes/update', [AdminShowtimeController::class, 'update']],
    ['POST', '/admin/showtimes/cancel', [AdminShowtimeController::class, 'cancel']],
    ['GET', '/admin/bookings', [AdminBookingController::class, 'index']],
    ['GET', '/admin/bookings/show', [AdminBookingController::class, 'show']],
    ['POST', '/admin/bookings/status', [AdminBookingController::class, 'status']],
    ['GET', '/admin/users', [AdminUserController::class, 'index']],
    ['POST', '/admin/users/status', [AdminUserController::class, 'status']],
    ['POST', '/admin/users/role', [AdminUserController::class, 'role']],
    ['GET', '/admin/reports', [AdminReportController::class, 'index']],
    ['GET', '/admin/reports/export', [AdminReportController::class, 'export']],
    ['GET', '/admin/settings', [AdminSettingsController::class, 'index']],
    ['POST', '/admin/settings', [AdminSettingsController::class, 'update']],
];
