<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        $companies = Company::query()
            ->withCount(['users', 'spaces', 'bookings'])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'contact_email', 'created_at']);

        return Inertia::render('Superadmin/Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Superadmin/Companies/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:companies,slug'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email'],
            'admin_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($data) {
            $slug = $data['slug'] ?? Str::slug($data['company_name']).'-'.Str::lower(Str::random(4));

            $company = Company::create([
                'name' => $data['company_name'],
                'slug' => $slug,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
            ]);

            User::create([
                'company_id' => $company->id,
                'role' => 'admin',
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'email_verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Empresa creada con su administrador inicial.');
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('Superadmin/Companies/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'contact_email' => $company->contact_email,
                'contact_phone' => $company->contact_phone,
                'description' => $company->description,
            ],
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('companies', 'slug')->ignore($company->id),
            ],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $company->update($data);

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Empresa actualizada.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('superadmin.companies.index')
            ->with('success', 'Empresa eliminada (junto con sus usuarios, espacios y reservas).');
    }

    /**
     * Acceder al panel de admin de una empresa como super-admin.
     * Guarda en sesión el ID del super-admin para poder volver después.
     */
    public function access(Request $request, Company $company): RedirectResponse
    {
        $admin = User::query()
            ->withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('role', 'admin')
            ->first();

        if (! $admin) {
            return back()->with('error', "La empresa \"{$company->name}\" no tiene un administrador asignado.");
        }

        // Guardar el ID del super-admin original para poder volver
        $request->session()->put('impersonator_id', $request->user()->id);

        Auth::login($admin);

        return redirect()
            ->route('dashboard')
            ->with('success', "Has accedido al panel de \"{$company->name}\" como su administrador.");
    }

    /**
     * Volver a la sesión del super-admin original.
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $originalId = $request->session()->get('impersonator_id');

        if (! $originalId) {
            return redirect()->route('dashboard');
        }

        $original = User::query()->withoutGlobalScopes()->find($originalId);

        $request->session()->forget('impersonator_id');

        if (! $original || ! $original->isSuperadmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        Auth::login($original);

        return redirect()->route('superadmin.dashboard');
    }
}
