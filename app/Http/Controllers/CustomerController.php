<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'company_id' => $request->user()->company_id,
            'role' => 'customer',
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(), // El admin lo da de alta, asume verificado
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente añadido. Comparte la contraseña con él para que pueda entrar.');
    }

    public function destroy(Request $request, User $customer): RedirectResponse
    {
        abort_unless(
            $customer->role === 'customer' && $customer->company_id === $request->user()->company_id,
            404,
        );

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente eliminado.');
    }
}
