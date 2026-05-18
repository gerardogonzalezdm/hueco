<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_only_superadmin_can_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($admin)->get('/superadmin/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/superadmin/dashboard')->assertForbidden();
        $this->actingAs($superadmin)
            ->get('/superadmin/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Superadmin/Dashboard'));
    }

    public function test_superadmin_can_create_company_with_initial_admin(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->post('/superadmin/companies', [
                'company_name' => 'Nuevo Cowork',
                'slug' => 'nuevo-cowork',
                'contact_email' => 'hola@nuevo.com',
                'contact_phone' => '+34 600',
                'admin_name' => 'Admin Inicial',
                'admin_email' => 'admin@nuevo.com',
                'admin_password' => 'password123',
                'admin_password_confirmation' => 'password123',
            ])->assertRedirect('/superadmin/companies');

        $this->assertDatabaseHas('companies', [
            'name' => 'Nuevo Cowork',
            'slug' => 'nuevo-cowork',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@nuevo.com',
            'role' => 'admin',
        ]);
    }

    public function test_superadmin_can_delete_company_and_cascade(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $company = Company::factory()->create();
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($superadmin)
            ->delete("/superadmin/companies/{$company->id}")
            ->assertRedirect('/superadmin/companies');

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_superadmin_can_add_another_superadmin(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->post('/superadmin/admins', [
                'name' => 'Segundo Super',
                'email' => 'super2@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertRedirect('/superadmin/admins');

        $this->assertDatabaseHas('users', [
            'email' => 'super2@example.com',
            'role' => 'superadmin',
            'company_id' => null,
        ]);
    }

    public function test_superadmin_cannot_delete_themselves(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        // Crear otro para que no quede solo uno
        User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->delete("/superadmin/admins/{$superadmin->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_cannot_delete_last_superadmin(): void
    {
        $onlySuperadmin = User::factory()->superadmin()->create();

        $this->actingAs($onlySuperadmin)
            ->delete("/superadmin/admins/{$onlySuperadmin->id}")
            ->assertStatus(422);
    }

    public function test_admin_cannot_login_via_admins_endpoint_to_become_superadmin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/superadmin/admins', [
                'name' => 'Intruso',
                'email' => 'x@x.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertForbidden();
    }

    public function test_superadmin_can_impersonate_company_admin_and_return(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $company = Company::factory()->create();
        $admin = User::factory()->admin()->for($company)->create();

        // Accede como admin
        $this->actingAs($superadmin)
            ->post("/superadmin/companies/{$company->id}/access")
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($admin);

        // Vuelve al super-admin
        $this->post('/superadmin/stop-impersonating')
            ->assertRedirect('/superadmin/dashboard');

        $this->assertAuthenticatedAs($superadmin);
    }

    public function test_impersonate_fails_when_company_has_no_admin(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $company = Company::factory()->create();
        // sin crear admin

        $this->actingAs($superadmin)
            ->post("/superadmin/companies/{$company->id}/access")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertAuthenticatedAs($superadmin);
    }
}
