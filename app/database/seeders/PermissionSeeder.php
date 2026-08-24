<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * 4 roles reales del panel (docs/01-analisis-descubrimiento.md §E pregunta #2):
 * administrador, editor general, editor de noticias/banners + marketing,
 * editor académico. No son 2 roles binarios como en IPG.
 */
class PermissionSeeder extends Seeder
{
    private array $modules = [
        'pages', 'posts', 'documents', 'announcements', 'calendar_events',
        'galleries', 'media', 'categories', 'menus', 'redirects',
        'settings', 'users', 'form_submissions', 'activity_log',
    ];

    private array $actions = ['view', 'create', 'update', 'delete', 'publish'];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            foreach ($this->actions as $action) {
                Permission::findOrCreate("{$module}.{$action}");
            }
        }

        $admin = Role::findOrCreate('administrador');
        $admin->syncPermissions(Permission::all());

        $editorGeneral = Role::findOrCreate('editor_general');
        $editorGeneral->syncPermissions($this->permissionsFor([
            'pages', 'documents', 'categories', 'media', 'galleries', 'form_submissions',
        ], ['view', 'create', 'update', 'publish']));

        $editorNoticiasMarketing = Role::findOrCreate('editor_noticias_marketing');
        $editorNoticiasMarketing->syncPermissions([
            ...$this->permissionsFor(['posts', 'media'], ['view', 'create', 'update', 'delete', 'publish']),
            ...$this->permissionsFor(['settings'], ['view', 'update']), // IDs de Analytics/Ads/Meta
        ]);

        $editorAcademico = Role::findOrCreate('editor_academico');
        $editorAcademico->syncPermissions($this->permissionsFor([
            'calendar_events', 'announcements', 'documents',
        ], ['view', 'create', 'update', 'publish']));
    }

    private function permissionsFor(array $modules, array $actions): array
    {
        $names = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        return Permission::whereIn('name', $names)->get()->all();
    }
}
