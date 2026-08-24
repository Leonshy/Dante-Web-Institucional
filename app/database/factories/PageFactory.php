<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => ['es' => $title],
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'site_section' => 'general',
            'status' => 'draft',
            'blocks' => [],
        ];
    }
}
