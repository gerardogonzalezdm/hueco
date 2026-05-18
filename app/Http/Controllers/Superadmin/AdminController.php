<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $admins = User::query()
            ->withoutGlobalScopes()
            ->where('role', 'superadmin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        return Inertia::render('Superadmin/Admins/Index', [
            'admins' => $admins,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Superadmin/Admins/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'company_id' => null,
            'role' => 'superadmin',
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Super-admin añadido.');
    }

    public function destroy(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->isSuperadmin(), 404);
        abort_if($admin->id === $request->user()->id, 422, 'No puedes eliminar tu propia cuenta.');

        // Garantizar que siempre quede al menos un super-admin
        $total = User::query()->withoutGlobalScopes()->where('role', 'superadmin')->count();
        abort_if($total <= 1, 422, 'Debe quedar al menos un super-admin.');

        $admin->delete();

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Super-admin eliminado.');
    }
}
