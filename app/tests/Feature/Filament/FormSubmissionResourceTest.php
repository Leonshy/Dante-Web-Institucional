<?php

use App\Filament\Resources\FormSubmissions\Pages\EditFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Models\FormSubmission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los envíos de formularios en el panel', function () {
    FormSubmission::query()->create([
        'type' => 'contacto',
        'name' => 'Familia Gómez',
        'email' => 'familia@example.com',
        'message' => 'Quisiera más información',
        'status' => 'nuevo',
    ]);

    $this->livewire(ListFormSubmissions::class)->assertSuccessful();
});

it('permite marcar un envío como leído o respondido, sin editar los datos del remitente', function () {
    $submission = FormSubmission::query()->create([
        'type' => 'pre_inscripcion',
        'name' => 'Familia Benítez',
        'email' => 'benitez@example.com',
        'site' => 'asuncion',
        'message' => 'Quiero pre-inscribir a mi hijo',
        'status' => 'nuevo',
    ]);

    $this->livewire(EditFormSubmission::class, ['record' => $submission->getRouteKey()])
        ->fillForm(['status' => 'respondido'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($submission->refresh()->status)->toBe('respondido')
        ->and($submission->name)->toBe('Familia Benítez');
});

it('un usuario sin el permiso de form_submissions no puede ver los envíos', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListFormSubmissions::class)->assertForbidden();
});

it('el editor general sí puede ver los envíos de formularios', function () {
    $editorGeneral = User::factory()->create(['is_active' => true]);
    $editorGeneral->assignRole('editor_general');

    $this->actingAs($editorGeneral);

    $this->livewire(ListFormSubmissions::class)->assertSuccessful();
});
