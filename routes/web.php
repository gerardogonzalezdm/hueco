<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CompanyController as PublicCompanyController;
use App\Http\Controllers\Public\PublicBookingController;
use App\Http\Controllers\SpaceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('spaces', SpaceController::class)->except('show');
    Route::resource('bookings', BookingController::class)->except('show');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::patch('/calendar/bookings/{booking}', [CalendarController::class, 'reschedule'])
        ->name('calendar.reschedule');

    Route::get('/settings/company', [CompanySettingsController::class, 'edit'])
        ->name('settings.company.edit');
    Route::patch('/settings/company', [CompanySettingsController::class, 'update'])
        ->name('settings.company.update');
});

// Portal público de cada empresa cliente
Route::prefix('c/{company:slug}')->name('public.')->group(function () {
    Route::get('/', [PublicCompanyController::class, 'show'])->name('company.show');
    Route::get('/reservar', [PublicBookingController::class, 'create'])->name('bookings.create');
    Route::post('/reservar', [PublicBookingController::class, 'store'])->name('bookings.store');
});

// Gestión de reserva por token (link enviado por email)
Route::get('/r/{token}', [PublicBookingController::class, 'show'])->name('public.bookings.show');
Route::post('/r/{token}/cancelar', [PublicBookingController::class, 'cancel'])->name('public.bookings.cancel');

require __DIR__.'/auth.php';
