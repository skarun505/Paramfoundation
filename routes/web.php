<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\TicketController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SlotController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ReportController;

// ── Public / Customer ──────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/book', [BookingController::class, 'slots'])->name('customer.slots');
Route::get('/book/{slot}/checkout', [BookingController::class, 'checkout'])->name('customer.checkout');
Route::post('/book/confirm', [BookingController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('customer.confirm');

// Ticket — require phone match (basic ownership check via session token)
Route::get('/ticket/{booking}', [TicketController::class, 'show'])->name('customer.ticket');

// ── Scanner (PIN-protected) ───────────────────────────────────────────────────
Route::prefix('scanner')->name('scanner.')->group(function () {
    Route::get('/login', [ScannerController::class, 'loginForm'])->name('login');
    Route::post('/login', [ScannerController::class, 'login'])
        ->middleware('throttle:5,1')           // max 5 PIN attempts per minute
        ->name('login.post');
    Route::post('/logout', [ScannerController::class, 'logout'])->name('logout');

    // Protected scanner routes
    Route::middleware('scanner')->group(function () {
        Route::get('/', [ScannerController::class, 'index'])->name('index');
        Route::get('/verify/{code}', [ScannerController::class, 'verify'])->name('verify');
        Route::get('/live', [ScannerController::class, 'liveCount'])->name('live');
    });
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Slots
        Route::resource('slots', SlotController::class);
        Route::post('slots/bulk', [SlotController::class, 'bulkCreate'])->name('slots.bulk');

        // Bookings
        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/export', [AdminBookingController::class, 'export'])->name('bookings.export');
        Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

// ── Auth (Breeze) — Registration DISABLED for production ──────────────────────
Route::middleware('guest')->group(function () {
    // Login only — no public registration
    Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::get('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    Route::get('verify-email', [\App\Http\Controllers\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])
        ->name('password.update');
});
