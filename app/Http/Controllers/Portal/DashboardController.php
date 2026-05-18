<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $upcoming = Booking::withoutGlobalScopes()
            ->with('space:id,name')
            ->where('user_id', $user->id)
            ->where('time_start', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('time_start')
            ->limit(5)
            ->get();

        $stats = [
            'upcoming' => $upcoming->count(),
            'total' => Booking::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->count(),
        ];

        return Inertia::render('Portal/Dashboard', [
            'company' => $user->company()->select('id', 'name', 'slug')->first(),
            'stats' => $stats,
            'upcoming' => $upcoming,
        ]);
    }
}
