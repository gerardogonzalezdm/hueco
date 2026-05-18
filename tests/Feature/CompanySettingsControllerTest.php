<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_view_settings_page(): void
    {
        $company = Company::factory()->create(['name' => 'Mi Cowork']);
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->get('/settings/company')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Company')
                ->where('company.name', 'Mi Cowork')
            );
    }

    public function test_admin_can_update_company_settings(): void
    {
        $company = Company::factory()->create(['slug' => 'old-slug']);
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->patch('/settings/company', [
                'name' => 'Cowork Renombrado',
                'slug' => 'cowork-renombrado',
                'contact_email' => 'hola@cowork.com',
                'contact_phone' => '+34 600 000 000',
                'description' => 'Un cowork acogedor en el centro.',
            ])
            ->assertRedirect('/settings/company');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Cowork Renombrado',
            'slug' => 'cowork-renombrado',
            'contact_email' => 'hola@cowork.com',
        ]);
    }

    public function test_validation_rejects_invalid_slug(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->patch('/settings/company', [
                'name' => 'X',
                'slug' => 'Slug INVALIDO con espacios',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_slug_must_be_unique_across_companies(): void
    {
        $companyOther = Company::factory()->create(['slug' => 'reservada']);
        $companyMine = Company::factory()->create(['slug' => 'mi-slug']);
        $admin = User::factory()->admin()->for($companyMine)->create();

        $this->actingAs($admin)
            ->patch('/settings/company', [
                'name' => 'X',
                'slug' => 'reservada',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_can_keep_same_slug(): void
    {
        $company = Company::factory()->create(['slug' => 'mismo-slug']);
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->patch('/settings/company', [
                'name' => 'Nuevo nombre',
                'slug' => 'mismo-slug',
            ])
            ->assertRedirect('/settings/company')
            ->assertSessionHasNoErrors();
    }

    public function test_non_admin_cannot_update_settings(): void
    {
        $regular = User::factory()->create();

        $this->actingAs($regular)
            ->patch('/settings/company', [
                'name' => 'Intento',
                'slug' => 'intento',
            ])
            ->assertForbidden();
    }
}
