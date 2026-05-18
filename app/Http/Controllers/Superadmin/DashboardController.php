<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $stats = [
            'companies' => Company::count(),
            'admins' => User::query()->withoutGlobalScopes()->where('role', 'admin')->count(),
            'customers' => User::query()->withoutGlobalScopes()->where('role', 'customer')->count(),
            'superadmins' => User::query()->withoutGlobalScopes()->where('role', 'superadmin')->count(),
            'spaces' => Space::query()->withoutGlobalScopes()->count(),
            'bookings_total' => Booking::query()->withoutGlobalScopes()->count(),
            'bookings_upcoming' => Booking::query()
                ->withoutGlobalScopes()
                ->where('time_start', '>=', now())
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        // Top empresas por número de reservas
        $topCompanies = Company::query()
            ->withCount(['bookings as bookings_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Superadmin/Dashboard', [
            'stats' => $stats,
            'top_companies' => $topCompanies,
        ]);
    }
}
