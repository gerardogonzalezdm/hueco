<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+2 weeks');
        $end = (clone $start)->modify('+60 minutes');

        return [
            'company_id' => Company::factory(),
            'space_id' => Space::factory(),
            'user_id' => null,
            'client_name' => fake()->name(),
            'client_email' => fake()->safeEmail(),
            'client_phone' => fake()->optional()->phoneNumber(),
            'client_notes' => null,
            'time_start' => $start,
            'time_end' => $end,
            'status' => 'confirmed',
            'google_event_id' => null,
        ];
    }
}
