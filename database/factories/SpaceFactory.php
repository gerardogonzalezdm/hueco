<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Space>
 */
class SpaceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Sala A', 'Sala B', 'Despacho', 'Cabina', 'Coworking']);

        return [
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'duration_minutes' => 60,
            'fixed_duration' => true,
            'price' => fake()->randomFloat(2, 5, 50),
            'show_price' => true,
            'show_duration' => true,
        ];
    }
}
