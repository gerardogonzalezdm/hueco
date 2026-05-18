<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanySettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $company = $request->user()->company;

        abort_if($company === null, 404);

        return Inertia::render('Settings/Company', [
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
                'contact_email' => $company->contact_email,
                'contact_phone' => $company->contact_phone,
                'description' => $company->description,
            ],
            'public_url' => route('public.company.show', $company->slug),
        ]);
    }

    public function update(UpdateCompanySettingsRequest $request): RedirectResponse
    {
        $company = $request->user()->company;

        abort_if($company === null, 404);
        abort_unless($request->user()->isAdmin(), 403);

        $company->update($request->validated());

        return redirect()
            ->route('settings.company.edit')
            ->with('success', 'Ajustes guardados.');
    }
}
