<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $fileName = Str::uuid()->toString().'.webp';

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word().'.webp',
            'file_name' => $fileName,
            'mime_type' => 'image/webp',
            'path' => 'general/'.$fileName,
            'disk' => 'media',
            'size' => $this->faker->numberBetween(1000, 500000),
            'type' => 'image',
            'alt' => $this->faker->sentence(3),
            'folder' => 'general',
        ];
    }
}
