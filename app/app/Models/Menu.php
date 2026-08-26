<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasAuditing, HasFactory;

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Estructura consumida por `site-header`/`site-footer` — mismo formato de
     * array que antes servía `config('navigation.*')`, para no tocar las
     * vistas al pasar del archivo fijo al menú administrable desde el panel.
     *
     * @return array<int, array{label: string, url: string, linkable: bool, children: array<int, array{label: string, url: string}>}>
     */
    public static function renderTree(string $key): array
    {
        $menu = self::query()
            ->where('key', $key)
            ->with([
                'items' => fn ($query) => $query->where('is_active', true),
                'items.children' => fn ($query) => $query->where('is_active', true),
            ])
            ->first();

        if (! $menu) {
            return [];
        }

        return $menu->items->map(fn (MenuItem $item) => [
            'label' => $item->label,
            'url' => $item->resolvedUrl(),
            'linkable' => $item->resolvedUrl() !== '#',
            'children' => $item->children->map(fn (MenuItem $child) => [
                'label' => $child->label,
                'url' => $child->resolvedUrl(),
            ])->all(),
        ])->all();
    }
}
