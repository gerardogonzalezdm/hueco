<?php

namespace Tests\Feature;

use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    public function test_customer_can_self_register_choosing_a_company(): void
    {
        $company = Company::factory()->create();

        $this->post('/customer/register', [
            'company_id' => $company->id,
            'name' => 'Cliente Nuevo',
            'email' => 'cliente@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/portal/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'cliente@example.com',
            'company_id' => $company->id,
            'role' => 'customer',
        ]);
    }

    public function test_customer_login_via_global_endpoint_redirects_to_portal(): void
    {
        $company = Company::factory()->create();
        $customer = User::factory()->customer()->for($company)->create([
            'email' => 'cli@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'cli@example.com',
            'password' => 'password123',
        ])->assertRedirect('/portal/dashboard');

        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_login_via_global_endpoint_redirects_to_admin_dashboard(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin()->for($company)->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_superadmin_login_redirects_to_superadmin_dashboard(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'email' => 'super@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'super@example.com',
            'password' => 'password123',
        ])->assertRedirect('/superadmin/dashboard');

        $this->assertAuthenticatedAs($superadmin);
    }

    public function test_customer_dashboard_is_accessible_only_to_customers(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin()->for($company)->create();
        $customer = User::factory()->customer()->for($company)->create();

        $this->actingAs($admin)
            ->get('/portal/dashboard')
            ->assertForbidden();

        $this->actingAs($customer)
            ->get('/portal/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Portal/Dashboard'));
    }

    public function test_admin_dashboard_redirects_customer_to_portal(): void
    {
        $company = Company::factory()->create();
        $customer = User::factory()->customer()->for($company)->create();

        $this->actingAs($customer)
            ->get('/dashboard')
            ->assertRedirect('/portal/dashboard');
    }

    public function test_admin_dashboard_redirects_superadmin_to_panel(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->get('/dashboard')
            ->assertRedirect('/superadmin/dashboard');
    }

    public function test_customer_can_create_booking_for_their_company(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $customer = User::factory()->customer()->for($company)->create([
            'name' => 'Cliente',
            'email' => 'cli@example.com',
        ]);

        $this->actingAs($customer)
            ->post('/portal/bookings', [
                'space_id' => $space->id,
                'time_start' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
                'duration_minutes' => 60,
            ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'space_id' => $space->id,
            'company_id' => $company->id,
            'user_id' => $customer->id,
            'client_email' => 'cli@example.com',
            'status' => 'confirmed',
        ]);

        Mail::assertSent(BookingConfirmedMail::class);
    }

    public function test_customer_only_sees_their_own_bookings(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $customerA = User::factory()->customer()->for($company)->create();
        $customerB = User::factory()->customer()->for($company)->create();

        Booking::factory()->for($company)->for($space)->create(['user_id' => $customerA->id]);
        Booking::factory()->for($company)->for($space)->create(['user_id' => $customerA->id]);
        Booking::factory()->for($company)->for($space)->create(['user_id' => $customerB->id]);

        $this->actingAs($customerA)
            ->get('/portal/bookings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Portal/Bookings/Index')
                ->has('bookings', 2)
            );
    }

    public function test_customer_can_cancel_their_own_booking_and_email_is_sent(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $customer = User::factory()->customer()->for($company)->create([
            'email' => 'cli@example.com',
        ]);
        $booking = Booking::factory()->for($company)->for($space)->create([
            'user_id' => $customer->id,
            'client_email' => 'cli@example.com',
            'status' => 'confirmed',
        ]);

        $this->actingAs($customer)
            ->post("/portal/bookings/{$booking->id}/cancel")
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        Mail::assertSent(BookingCancelledMail::class);
    }

    public function test_customer_cannot_access_admin_zones(): void
    {
        $company = Company::factory()->create();
        $customer = User::factory()->customer()->for($company)->create();

        $this->actingAs($customer)->get('/spaces')->assertForbidden();
        $this->actingAs($customer)->get('/bookings')->assertForbidden();
        $this->actingAs($customer)->get('/calendar')->assertForbidden();
        $this->actingAs($customer)->get('/customers')->assertForbidden();
        $this->actingAs($customer)->get('/settings/company')->assertForbidden();
        $this->actingAs($customer)->get('/superadmin/dashboard')->assertForbidden();
    }
}
