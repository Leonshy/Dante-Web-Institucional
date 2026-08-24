<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'title' => ['es' => $title],
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'excerpt' => ['es' => $this->faker->sentence()],
            'content' => ['es' => '<p>'.$this->faker->paragraph().'</p>'],
            'published_at' => now(),
            'status' => 'draft',
        ];
    }
}
