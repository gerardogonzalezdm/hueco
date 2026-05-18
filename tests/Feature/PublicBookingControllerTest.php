<?php

namespace Tests\Feature;

use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    public function test_company_landing_is_publicly_accessible(): void
    {
        $company = Company::factory()->create(['slug' => 'cowork-test']);
        Space::factory()->for($company)->create();

        $this->get("/c/{$company->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Public/CompanyLanding'));
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/c/inexistente')->assertNotFound();
    }

    public function test_visitor_can_create_a_booking_through_public_portal(): void
    {
        $company = Company::factory()->create(['slug' => 'cowork-test']);
        $space = Space::factory()->for($company)->create();

        $this->post("/c/{$company->slug}/reservar", [
            'space_id' => $space->id,
            'client_name' => 'Visitante',
            'client_email' => 'visit@example.com',
            'time_start' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'company_id' => $company->id,
            'space_id' => $space->id,
            'user_id' => null,
            'client_email' => 'visit@example.com',
            'status' => 'confirmed',
        ]);

        Mail::assertSent(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo('visit@example.com'));
    }

    public function test_public_booking_requires_future_date(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();

        $this->post("/c/{$company->slug}/reservar", [
            'space_id' => $space->id,
            'client_name' => 'Pasado',
            'client_email' => 'past@example.com',
            'time_start' => now()->subDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
        ])->assertSessionHasErrors('time_start');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_overlapping_public_booking_is_rejected(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();

        $existingStart = now()->addDays(2)->setTime(10, 0);
        Booking::factory()->for($company)->for($space)->create([
            'time_start' => $existingStart,
            'time_end' => $existingStart->copy()->addHour(),
            'status' => 'confirmed',
        ]);

        $this->post("/c/{$company->slug}/reservar", [
            'space_id' => $space->id,
            'client_name' => 'Choca',
            'client_email' => 'choca@example.com',
            'time_start' => $existingStart->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
        ])->assertSessionHasErrors('time_start');

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_visitor_can_view_booking_with_valid_token(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $booking = Booking::factory()->for($company)->for($space)->create([
            'manage_token' => 'abcdef123456',
        ]);

        $this->get('/r/abcdef123456')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/MyBooking')
                ->where('booking.id', $booking->id)
            );
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get('/r/no-existe-este-token')->assertNotFound();
    }

    public function test_visitor_can_cancel_booking_with_token(): void
    {
        $company = Company::factory()->create();
        $space = Space::factory()->for($company)->create();
        $booking = Booking::factory()->for($company)->for($space)->create([
            'manage_token' => 'tokencancel',
            'client_email' => 'cancel@example.com',
            'status' => 'confirmed',
        ]);

        $this->post('/r/tokencancel/cancelar')
            ->assertRedirect('/r/tokencancel');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        Mail::assertSent(BookingCancelledMail::class, fn ($mail) => $mail->hasTo('cancel@example.com'));
    }
}
