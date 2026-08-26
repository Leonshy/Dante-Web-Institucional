<?php

use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\EditAnnouncement;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los comunicados en el panel', function () {
    Announcement::factory()->count(2)->create();

    $this->livewire(ListAnnouncements::class)->assertSuccessful();
});

it('crea un comunicado con título, contenido y audiencia', function () {
    $this->livewire(CreateAnnouncement::class)
        ->fillForm([
            'title' => ['es' => 'Suspensión de clases'],
            'content' => ['es' => '<p>Por lluvia se suspenden las clases mañana.</p>'],
            'audience' => 'asuncion',
            'published_at' => now()->toDateString(),
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Announcement::query()->whereJsonContains('title->es', 'Suspensión de clases')->exists())->toBeTrue();
});

it('exige el título y el contenido del comunicado', function () {
    $this->livewire(CreateAnnouncement::class)
        ->fillForm([
            'title' => ['es' => ''],
            'content' => ['es' => ''],
            'audience' => 'toda-la-comunidad',
            'published_at' => now()->toDateString(),
        ])
        ->call('create')
        ->assertHasFormErrors(['title.es' => 'required', 'content.es']);
});

it('sanitiza el HTML del comunicado antes de guardarlo', function () {
    $this->livewire(CreateAnnouncement::class)
        ->fillForm([
            'title' => ['es' => 'Comunicado con script'],
            'content' => ['es' => '<p>Texto</p><script>alert(1)</script>'],
            'audience' => 'toda-la-comunidad',
            'published_at' => now()->toDateString(),
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $announcement = Announcement::query()->whereJsonContains('title->es', 'Comunicado con script')->firstOrFail();

    expect($announcement->getTranslation('content', 'es'))->not->toContain('<script');
});

it('edita el estado de un comunicado existente', function () {
    $announcement = Announcement::factory()->create(['status' => 'draft']);

    $this->livewire(EditAnnouncement::class, ['record' => $announcement->getRouteKey()])
        ->fillForm(['status' => 'published'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($announcement->refresh()->status)->toBe('published');
});

it('un usuario sin permiso no puede ver el listado de comunicados', function () {
    $editorNoticias = User::factory()->create(['is_active' => true]);
    $editorNoticias->assignRole('editor_noticias_marketing');

    $this->actingAs($editorNoticias);

    $this->livewire(ListAnnouncements::class)->assertForbidden();
});

it('el editor académico sí puede gestionar comunicados', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListAnnouncements::class)->assertSuccessful();
});

it('el editor académico no puede borrar comunicados (permiso real, no solo el botón oculto)', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');
    $announcement = Announcement::factory()->create();

    expect($editorAcademico->can('delete', $announcement))->toBeFalse();
});
