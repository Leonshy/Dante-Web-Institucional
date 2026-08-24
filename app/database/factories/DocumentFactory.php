<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'media_id' => Media::factory(),
            'title' => ['es' => $this->faker->sentence(3)],
            'site' => 'ambas',
            'published_at' => now(),
            'is_current' => true,
            'status' => 'draft',
        ];
    }
}
