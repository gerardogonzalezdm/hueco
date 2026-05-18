<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->company_id;

        $customers = User::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('role', 'customer')
            ->withCount([
                'bookings',
                'bookings as upcoming_bookings_count' => function ($q) {
                    $q->where('time_start', '>=', now())
                      ->where('status', '!=', 'cancelled');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }
}
