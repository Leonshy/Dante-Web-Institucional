<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocaleFields;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Spatie\Translatable\HasTranslations;

/**
 * Configuración administrable del home — hero (slide único o carrusel),
 * cifras, orden/activación de secciones y CTA final. Fila única (singleton,
 * `current()`), sin Resource de Filament con tabla: se administra desde
 * `App\Filament\Pages\HomeSettings`.
 *
 * @property array<int, array<string, mixed>>|null $hero_slides
 * @property array<int, array<string, mixed>>|null $stats
 * @property array<int, array{key: string, enabled: bool}>|null $sections
 */
#[Fillable(['hero_slides', 'stats', 'sections', 'cta_title', 'cta_text', 'cta_button_label', 'cta_button_url'])]
class HomeSetting extends Model
{
    use HasTranslations, ResolvesLocaleFields;

    public array $translatable = ['cta_title', 'cta_text', 'cta_button_label'];

    /**
     * Orden y claves por defecto de las secciones "de adelanto" del home.
     * `HomeSetting::current()` completa cualquier clave que falte al final,
     * así una clave nueva agregada más adelante no rompe datos ya guardados.
     *
     * "Cifras" no está acá: su posición es fija, justo debajo de "Nuestra
     * propuesta educativa" (pedido explícito del cliente, no reordenable ni
     * desactivable desde este listado) — se renderiza aparte en home.blade.php.
     */
    public const DEFAULT_SECTIONS = [
        ['key' => 'featured_pages', 'enabled' => true],
        ['key' => 'news', 'enabled' => true],
        ['key' => 'announcements', 'enabled' => true],
        ['key' => 'documents', 'enabled' => true],
        ['key' => 'gallery', 'enabled' => true],
    ];

    public const SECTION_LABELS = [
        'featured_pages' => 'Páginas destacadas',
        'news' => 'Noticias',
        'announcements' => 'Comunicados',
        'documents' => 'Documentos',
        'gallery' => 'Galería',
    ];

    protected function casts(): array
    {
        return [
            'hero_slides' => 'array',
            'stats' => 'array',
            'sections' => 'array',
        ];
    }

    public static function current(): self
    {
        $setting = static::query()->firstOrCreate([], ['sections' => self::DEFAULT_SECTIONS]);

        $sections = collect($setting->sections ?? [])->reject(fn (array $section) => $section['key'] === 'stats');

        $existingKeys = $sections->pluck('key');
        $missing = collect(self::DEFAULT_SECTIONS)->reject(fn (array $section) => $existingKeys->contains($section['key']));

        if ($missing->isNotEmpty() || $sections->count() !== count($setting->sections ?? [])) {
            $setting->sections = [...$sections->values()->all(), ...$missing->values()->all()];
            $setting->save();
        }

        return $setting;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function heroSlidesForLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($this->hero_slides ?? [])
            ->map(fn (array $slide): array => self::resolveLocaleFields($slide, $locale))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function statsForLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($this->stats ?? [])
            ->map(fn (array $stat): array => self::resolveLocaleFields($stat, $locale))
            ->all();
    }

    /**
     * Claves de sección habilitadas, en el orden guardado.
     *
     * @return array<int, string>
     */
    public function enabledSectionsInOrder(): array
    {
        return collect($this->sections ?? [])
            ->filter(fn (array $section) => Arr::get($section, 'enabled', false))
            ->pluck('key')
            ->all();
    }
}
