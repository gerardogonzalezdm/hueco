<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Portal\Auth\AuthenticatedSessionController as PortalAuthenticatedSessionController;
use App\Http\Controllers\Portal\Auth\RegisteredCustomerController;
use App\Http\Controllers\Portal\BookingController as PortalBookingController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Public\CompanyController as PublicCompanyController;
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
    // Perfil: cualquier user logueado puede editar el suyo
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Zona admin: gestión de la empresa
    Route::middleware('admin')->group(function () {
        Route::resource('spaces', SpaceController::class)->except('show');
        Route::resource('bookings', BookingController::class)->except('show');

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::patch('/calendar/bookings/{booking}', [CalendarController::class, 'reschedule'])
            ->name('calendar.reschedule');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

        Route::get('/settings/company', [CompanySettingsController::class, 'edit'])
            ->name('settings.company.edit');
        Route::patch('/settings/company', [CompanySettingsController::class, 'update'])
            ->name('settings.company.update');
    });
});

// Portal público de cada empresa cliente
Route::prefix('c/{company:slug}')->name('public.')->group(function () {
    Route::get('/', [PublicCompanyController::class, 'show'])->name('company.show');

    // Auth de cliente para esta empresa
    Route::middleware('guest')->group(function () {
        Route::get('/login', [PortalAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [PortalAuthenticatedSessionController::class, 'store'])->name('login.store');
        Route::get('/register', [RegisteredCustomerController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredCustomerController::class, 'store'])->name('register.store');
    });
});

// Portal del cliente (zona autenticada)
Route::prefix('portal')->name('portal.')->middleware(['auth', 'customer'])->group(function () {
    Route::get('/dashboard', PortalDashboardController::class)->name('dashboard');

    Route::get('/bookings', [PortalBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [PortalBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [PortalBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [PortalBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [PortalBookingController::class, 'cancel'])->name('bookings.cancel');
});

// Logout del cliente
Route::post('/portal/logout', [PortalAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('portal.logout');

require __DIR__.'/auth.php';
