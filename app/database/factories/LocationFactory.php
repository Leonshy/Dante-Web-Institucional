<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'academic_email' => $this->faker->safeEmail(),
            'administrative_email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'schedule' => 'Lunes a viernes, 7:00 a 17:00',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
