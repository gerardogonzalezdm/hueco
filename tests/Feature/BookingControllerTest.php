<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_create_a_booking(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Pepe Perez',
                'client_email' => 'pepe@example.com',
                'time_start' => '2026-06-01 10:00:00',
                'duration_minutes' => 60,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'space_id' => $space->id,
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'client_name' => 'Pepe Perez',
            'time_end' => '2026-06-01 11:00:00',
            'status' => 'confirmed',
        ]);
    }

    public function test_booking_with_overlapping_time_is_rejected(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        Booking::factory()->for($company)->for($space)->create([
            'time_start' => '2026-06-01 10:00:00',
            'time_end' => '2026-06-01 11:00:00',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Choca',
                'client_email' => 'choca@example.com',
                'time_start' => '2026-06-01 10:30:00',
                'duration_minutes' => 60,
            ])
            ->assertSessionHasErrors('time_start');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_cancelled_bookings_do_not_block_new_ones(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        Booking::factory()->for($company)->for($space)->create([
            'time_start' => '2026-06-01 10:00:00',
            'time_end' => '2026-06-01 11:00:00',
            'status' => 'cancelled',
        ]);

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Reutiliza hueco',
                'client_email' => 'reuso@example.com',
                'time_start' => '2026-06-01 10:00:00',
                'duration_minutes' => 60,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_consecutive_bookings_without_overlap_are_allowed(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        Booking::factory()->for($company)->for($space)->create([
            'time_start' => '2026-06-01 10:00:00',
            'time_end' => '2026-06-01 11:00:00',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Justo despues',
                'client_email' => 'next@example.com',
                'time_start' => '2026-06-01 11:00:00',
                'duration_minutes' => 60,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_admin_cannot_create_booking_on_space_of_another_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $spaceB = Space::factory()->for($companyB)->create();
        $adminA = User::factory()->admin()->for($companyA)->create();

        $this->actingAs($adminA)
            ->post('/bookings', [
                'space_id' => $spaceB->id,
                'client_name' => 'Intruso',
                'client_email' => 'x@example.com',
                'time_start' => '2026-06-01 10:00:00',
                'duration_minutes' => 60,
            ])
            ->assertSessionHasErrors('space_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_only_sees_bookings_of_their_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Booking::factory()->for($companyA)->for(Space::factory()->for($companyA))->count(2)->create();
        Booking::factory()->for($companyB)->for(Space::factory()->for($companyB))->create();

        $adminA = User::factory()->admin()->for($companyA)->create();

        $this->actingAs($adminA)
            ->get('/bookings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Bookings/Index')
                ->has('bookings', 2)
            );
    }
}
