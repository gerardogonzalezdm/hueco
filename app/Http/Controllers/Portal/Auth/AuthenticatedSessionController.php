<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Company $company): Response
    {
        return Inertia::render('Portal/Auth/Login', [
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
            ],
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->withoutGlobalScopes()
            ->where('email', $credentials['email'])
            ->where('company_id', $company->id)
            ->where('role', 'customer')
            ->first();

        if (! $user || ! Auth::attempt([
            'email' => $user->email,
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con ningún cliente de esta empresa.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $company = $request->user()?->company;

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($company) {
            return redirect()->route('public.company.show', $company->slug);
        }

        return redirect('/');
    }
}
