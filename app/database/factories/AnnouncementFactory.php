<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'title' => ['es' => $this->faker->sentence(4)],
            'content' => ['es' => '<p>'.$this->faker->paragraph().'</p>'],
            'published_at' => now(),
            'audience' => 'toda-la-comunidad',
            'status' => 'draft',
        ];
    }
}
