<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        return [
            'title' => ['es' => $this->faker->sentence(3)],
            'starts_at' => now()->addDays(3),
            'level' => 'todo-el-colegio',
            'status' => 'draft',
        ];
    }
}
