<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label' => ['es' => $this->faker->words(2, true)],
            'url' => '/'.$this->faker->slug(),
            'linkable_type' => null,
            'linkable_id' => null,
            'sort_order' => 0,
            'open_in_new_tab' => false,
            'is_active' => true,
        ];
    }
}
