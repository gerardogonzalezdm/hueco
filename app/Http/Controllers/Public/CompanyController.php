<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Space;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Landing pública de una empresa.
     */
    public function show(Company $company): Response
    {
        return Inertia::render('Public/CompanyLanding', [
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
                'contact_email' => $company->contact_email,
                'contact_phone' => $company->contact_phone,
                'description' => $company->description,
            ],
            'spaces' => Space::query()
                ->withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->orderBy('name')
                ->get(['id', 'name', 'duration_minutes', 'fixed_duration', 'price', 'show_price', 'show_duration']),
        ]);
    }
}
