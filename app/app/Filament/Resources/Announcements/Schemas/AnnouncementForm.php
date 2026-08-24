<?php

namespace App\Filament\Resources\Announcements\Schemas;

use App\Models\SiteSetting;
use App\Services\Html\HtmlSanitizer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Comunicado')->schema([
                Tabs::make('idiomas')->tabs([
                    Tab::make('Español')->schema([
                        TextInput::make('title.es')->label('Título')->required()->maxLength(255),
                        RichEditor::make('content.es')->label('Contenido')->required(),
                    ]),
                    Tab::make('Italiano')->schema([
                        TextInput::make('title.it')->label('Título')->maxLength(255),
                        RichEditor::make('content.it')->label('Contenido'),
                    ])->visible(fn () => SiteSetting::italianEnabled()),
                ]),
            ]),
            Section::make('Difusión')->schema([
                Select::make('audience')
                    ->label('Dirigido a')
                    ->options([
                        'toda-la-comunidad' => 'Toda la comunidad',
                        'asuncion' => 'Solo Asunción',
                        'fernando-de-la-mora' => 'Solo Fernando de la Mora',
                    ])
                    ->default('toda-la-comunidad')
                    ->required(),
                DatePicker::make('published_at')->label('Fecha de publicación')->required(),
                DatePicker::make('valid_until')->label('Vigente hasta (opcional)'),
                Toggle::make('is_pinned')->label('Fijar arriba del listado'),
            ])->columns(2),
            Section::make('Publicación')->schema([
                Select::make('status')
                    ->label('Estado')
                    ->options(['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'])
                    ->default('draft')
                    ->required(),
            ]),
        ]);
    }

    public static function sanitize(array $data): array
    {
        if (isset($data['content']) && is_array($data['content'])) {
            $sanitizer = app(HtmlSanitizer::class);
            foreach ($data['content'] as $locale => $html) {
                $data['content'][$locale] = $sanitizer->clean($html);
            }
        }

        return $data;
    }
}
