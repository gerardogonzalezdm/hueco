<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Portal\BookingController as PortalBookingController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Public\CompanyController as PublicCompanyController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\Superadmin\AdminController as SuperadminAdminController;
use App\Http\Controllers\Superadmin\CompanyController as SuperadminCompanyController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard genérico: redirige según role
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Perfil: cualquier user logueado puede editar el suyo
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Zona admin de empresa
    Route::middleware('admin')->group(function () {
        Route::resource('spaces', SpaceController::class)->except('show');
        Route::resource('bookings', BookingController::class)->except('show');

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::patch('/calendar/bookings/{booking}', [CalendarController::class, 'reschedule'])
            ->name('calendar.reschedule');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/settings/company', [CompanySettingsController::class, 'edit'])
            ->name('settings.company.edit');
        Route::patch('/settings/company', [CompanySettingsController::class, 'update'])
            ->name('settings.company.update');
    });
});

// Landing pública informativa de cada empresa
Route::get('/c/{company:slug}', [PublicCompanyController::class, 'show'])
    ->name('public.company.show');

// Panel super-admin (zona reservada para dueños de Hueco)
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/dashboard', SuperadminDashboardController::class)->name('dashboard');

    Route::get('/companies', [SuperadminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [SuperadminCompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [SuperadminCompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [SuperadminCompanyController::class, 'edit'])->name('companies.edit');
    Route::patch('/companies/{company}', [SuperadminCompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [SuperadminCompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('/admins', [SuperadminAdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [SuperadminAdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [SuperadminAdminController::class, 'store'])->name('admins.store');
    Route::delete('/admins/{admin}', [SuperadminAdminController::class, 'destroy'])->name('admins.destroy');
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

require __DIR__.'/auth.php';
