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

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    public function test_admin_can_create_a_booking_for_walk_in_client_without_account(): void
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

        // Sin marcar 'create_account': la reserva queda con user_id null
        $this->assertDatabaseHas('bookings', [
            'space_id' => $space->id,
            'company_id' => $company->id,
            'user_id' => null,
            'client_name' => 'Pepe Perez',
            'time_end' => '2026-06-01 11:00:00',
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_creating_booking_associates_existing_customer_by_email(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();
        $existingCustomer = User::factory()->customer()->for($company)->create([
            'email' => 'cli@example.com',
        ]);

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Cualquier nombre',
                'client_email' => 'cli@example.com',
                'time_start' => '2026-06-01 10:00:00',
                'duration_minutes' => 60,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'space_id' => $space->id,
            'user_id' => $existingCustomer->id,
            'client_email' => 'cli@example.com',
        ]);
    }

    public function test_admin_can_create_customer_on_the_fly_when_booking(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Nuevo Cliente',
                'client_email' => 'nuevo@example.com',
                'client_phone' => '+34 600 000',
                'time_start' => '2026-06-02 09:00:00',
                'duration_minutes' => 60,
                'create_account' => true,
                'account_password' => 'password123',
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'role' => 'customer',
            'company_id' => $company->id,
        ]);

        $newCustomer = \App\Models\User::query()->withoutGlobalScopes()
            ->where('email', 'nuevo@example.com')->first();

        $this->assertDatabaseHas('bookings', [
            'space_id' => $space->id,
            'user_id' => $newCustomer->id,
        ]);
    }

    public function test_admin_can_create_flex_booking_with_time_end_directly(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create(['fixed_duration' => false]);
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'Cliente Flex',
                'client_email' => 'flex@example.com',
                'time_start' => '2026-06-03 14:30:00',
                'time_end' => '2026-06-03 17:15:00',
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'space_id' => $space->id,
            'time_start' => '2026-06-03 14:30:00',
            'time_end' => '2026-06-03 17:15:00',
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

    public function test_creating_a_booking_sends_confirmation_email_to_client(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->post('/bookings', [
                'space_id' => $space->id,
                'client_name' => 'María',
                'client_email' => 'maria@example.com',
                'time_start' => '2026-06-01 10:00:00',
                'duration_minutes' => 60,
            ])
            ->assertRedirect('/bookings');

        Mail::assertSent(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo('maria@example.com'));
    }

    public function test_cancelling_a_booking_via_update_sends_cancellation_email(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        $booking = Booking::factory()->for($company)->for($space)->create([
            'client_email' => 'cancel@example.com',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->put("/bookings/{$booking->id}", ['status' => 'cancelled'])
            ->assertRedirect('/bookings');

        Mail::assertSent(BookingCancelledMail::class, fn ($mail) => $mail->hasTo('cancel@example.com'));
    }

    public function test_deleting_a_non_cancelled_booking_sends_cancellation_email(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        $booking = Booking::factory()->for($company)->for($space)->create([
            'client_email' => 'gone@example.com',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->delete("/bookings/{$booking->id}")
            ->assertRedirect('/bookings');

        Mail::assertSent(BookingCancelledMail::class, fn ($mail) => $mail->hasTo('gone@example.com'));
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_deleting_already_cancelled_booking_does_not_send_email(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $admin = User::factory()->admin()->for($company)->create();

        $booking = Booking::factory()->for($company)->for($space)->create([
            'status' => 'cancelled',
        ]);

        $this->actingAs($admin)
            ->delete("/bookings/{$booking->id}")
            ->assertRedirect('/bookings');

        Mail::assertNothingSent();
    }
}
