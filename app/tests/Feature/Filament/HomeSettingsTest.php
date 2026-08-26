<?php

use App\Filament\Pages\HomeSettings;
use App\Models\HomeSetting;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('carga la página de ajustes del inicio', function () {
    $this->livewire(HomeSettings::class)->assertSuccessful();
});

it('guarda el hero, las cifras, el orden de secciones y el cta', function () {
    $media = Media::factory()->create();

    $this->livewire(HomeSettings::class)
        ->fillForm([
            'hero_slides' => [
                [
                    'media_id' => $media->id,
                    'title' => ['es' => 'Bienvenidos a Dante'],
                    'subtitle' => ['es' => 'Educación bilingüe'],
                    'cta_label' => ['es' => 'Conocer más'],
                    'cta_url' => '/admisiones',
                ],
            ],
            'stats' => [
                [
                    'value' => '129',
                    'symbol' => '°',
                    'title' => ['es' => 'Aniversario'],
                    'description' => ['es' => 'de la Scuola Dante Alighieri'],
                ],
            ],
            'sections' => [
                ['key' => 'news', 'enabled' => true],
                ['key' => 'featured_pages', 'enabled' => false],
                ['key' => 'announcements', 'enabled' => true],
                ['key' => 'documents', 'enabled' => true],
                ['key' => 'gallery', 'enabled' => true],
            ],
            'cta_title' => ['es' => '¿Quiere conocer el colegio?'],
            'cta_text' => ['es' => 'Complete la pre-inscripción y lo contactamos.'],
            'cta_button_label' => ['es' => 'Quiero inscribir a mi hijo/a'],
            'cta_button_url' => '/admisiones',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $setting = HomeSetting::current()->refresh();

    expect($setting->hero_slides[0]['media_id'])->toBe($media->id)
        ->and($setting->heroSlidesForLocale('es')[0]['title'] ?? null)->toBe('Bienvenidos a Dante')
        ->and($setting->stats[0]['value'])->toBe('129')
        ->and($setting->stats[0]['symbol'])->toBe('°')
        ->and($setting->enabledSectionsInOrder())->toBe(['news', 'announcements', 'documents', 'gallery'])
        ->and($setting->cta_title)->toBe('¿Quiere conocer el colegio?');
});

it('un usuario sin permiso no puede ver los ajustes del inicio', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    expect(HomeSettings::canAccess())->toBeFalse();
});
