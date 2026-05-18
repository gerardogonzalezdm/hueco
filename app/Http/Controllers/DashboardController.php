<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Space;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'company' => $user->company()->select('id', 'name')->first(),
            'stats' => [
                'spaces' => Space::count(),
                'bookings_total' => Booking::count(),
                'bookings_upcoming' => Booking::where('time_start', '>=', now())
                    ->where('status', '!=', 'cancelled')
                    ->count(),
            ],
            'upcoming' => Booking::with('space:id,name')
                ->where('time_start', '>=', now())
                ->where('status', '!=', 'cancelled')
                ->orderBy('time_start')
                ->limit(5)
                ->get(),
        ]);
    }
}
