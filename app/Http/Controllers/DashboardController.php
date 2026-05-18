<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperadmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->isCustomer()) {
            return redirect()->route('portal.dashboard');
        }

        $companyId = $user->company_id;

        $stats = [
            'spaces' => Space::count(),
            'bookings_total' => Booking::count(),
            'bookings_upcoming' => Booking::where('time_start', '>=', now())
                ->where('status', '!=', 'cancelled')
                ->count(),
            'customers' => User::query()
                ->withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('role', 'customer')
                ->count(),
        ];

        $upcoming = Booking::with('space:id,name')
            ->where('time_start', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('time_start')
            ->limit(5)
            ->get();

        // Top 5 espacios más reservados (excluyendo cancelados)
        $topSpaces = Space::query()
            ->withCount(['bookings' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'price']);

        // Top 5 clientes con más reservas (solo los que están registrados)
        $topCustomers = User::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('role', 'customer')
            ->withCount(['bookings as bookings_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'email']);

        // Ingresos del mes en curso (suma de price del space × cada booking confirmado)
        $monthRevenue = Booking::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('time_start', [now()->startOfMonth(), now()->endOfMonth()])
            ->join('spaces', 'bookings.space_id', '=', 'spaces.id')
            ->sum('spaces.price');

        // Reservas por día últimos 14 días (para gráfico de barras)
        $startDate = now()->subDays(13)->startOfDay();
        $bookingsRaw = Booking::query()
            ->where('time_start', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->get(['time_start']);

        $bookingsByDay = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $bookingsByDay[] = [
                'date' => $date,
                'count' => $bookingsRaw->filter(fn ($b) => $b->time_start->format('Y-m-d') === $date)->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'company' => $user->company()->select('id', 'name')->first(),
            'stats' => $stats,
            'upcoming' => $upcoming,
            'top_spaces' => $topSpaces,
            'top_customers' => $topCustomers,
            'month_revenue' => round((float) $monthRevenue, 2),
            'bookings_by_day' => $bookingsByDay,
        ]);
    }
}
