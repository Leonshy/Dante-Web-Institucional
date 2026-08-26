<?php

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista el registro de auditoría en el panel', function () {
    Page::factory()->create();

    $this->livewire(ListActivityLogs::class)
        ->assertSuccessful();
});

it('un usuario sin permiso no puede ver el registro de auditoría', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListActivityLogs::class)->assertForbidden();
});

it('registra el usuario y la fecha exactos al editar una página desde el panel', function () {
    $page = Page::factory()->create();

    $before = now();

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm(['title.es' => 'Título modificado desde el test de auditoría'])
        ->call('save')
        ->assertHasNoFormErrors();

    $activity = Activity::query()
        ->where('subject_type', Page::class)
        ->where('subject_id', $page->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($this->admin->id)
        ->and($activity->causer_type)->toBe(User::class)
        ->and($activity->created_at->diffInSeconds($before))->toBeLessThan(5)
        ->and($activity->changes()->get('attributes'))->toHaveKey('title');
});
