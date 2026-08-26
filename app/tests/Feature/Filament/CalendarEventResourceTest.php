<?php

use App\Filament\Resources\CalendarEvents\Pages\CreateCalendarEvent;
use App\Filament\Resources\CalendarEvents\Pages\EditCalendarEvent;
use App\Filament\Resources\CalendarEvents\Pages\ListCalendarEvents;
use App\Models\CalendarEvent;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los eventos del calendario en el panel', function () {
    CalendarEvent::factory()->count(2)->create();

    $this->livewire(ListCalendarEvents::class)->assertSuccessful();
});

it('crea un evento con título, fecha y nivel', function () {
    $this->livewire(CreateCalendarEvent::class)
        ->fillForm([
            'title' => ['es' => 'Acto de fin de año'],
            'starts_at' => now()->addDays(10)->toDateTimeString(),
            'level' => 'primaria',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(CalendarEvent::query()->whereJsonContains('title->es', 'Acto de fin de año')->exists())->toBeTrue();
});

it('exige el título y la fecha de comienzo', function () {
    $this->livewire(CreateCalendarEvent::class)
        ->fillForm([
            'title' => ['es' => ''],
            'starts_at' => null,
            'level' => 'primaria',
        ])
        ->call('create')
        ->assertHasFormErrors(['title.es' => 'required', 'starts_at' => 'required']);
});

it('edita el estado de publicación de un evento', function () {
    $event = CalendarEvent::factory()->create(['status' => 'draft']);

    $this->livewire(EditCalendarEvent::class, ['record' => $event->getRouteKey()])
        ->fillForm(['status' => 'published'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($event->refresh()->status)->toBe('published');
});

it('un usuario sin permiso de calendario no puede ver el listado', function () {
    $editorNoticias = User::factory()->create(['is_active' => true]);
    $editorNoticias->assignRole('editor_noticias_marketing');

    $this->actingAs($editorNoticias);

    $this->livewire(ListCalendarEvents::class)->assertForbidden();
});

it('el editor académico sí puede ver y crear eventos del calendario', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListCalendarEvents::class)->assertSuccessful();

    $this->livewire(CreateCalendarEvent::class)
        ->fillForm([
            'title' => ['es' => 'Reunión de padres'],
            'starts_at' => now()->addDays(5)->toDateTimeString(),
            'level' => 'inicial',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('el editor académico no puede borrar eventos del calendario (permiso real, no solo el botón oculto)', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');
    $event = CalendarEvent::factory()->create();

    expect($editorAcademico->can('delete', $event))->toBeFalse();
});
