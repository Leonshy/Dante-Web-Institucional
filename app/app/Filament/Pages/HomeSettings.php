<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\MediaPicker;
use App\Filament\Tables\MediaLibraryTable;
use App\Models\HomeSetting;
use App\Models\Page as PageModel;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * Ajustes administrables del home: hero (slide único o carrusel), cifras,
 * orden/activación de secciones y CTA final (ver plan de esta sesión). El
 * toggle "Destacar en el inicio" de cada Página vive en la propia página
 * (`PageForm`), no acá — esta pantalla solo lista cuáles están activas.
 *
 * @property-read Schema $form
 */
class HomeSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Inicio';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?string $title = 'Inicio';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.home-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('viewAny', HomeSetting::class);
    }

    public function mount(): void
    {
        $this->form->fill($this->getRecord()->attributesToArray());
    }

    public function getRecord(): HomeSetting
    {
        return HomeSetting::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Hero')
                        ->description('1 imagen = hero fijo. 2 o más = carrusel automático.')
                        ->schema([
                            Repeater::make('hero_slides')
                                ->hiddenLabel()
                                ->reorderable()
                                ->addActionLabel('Agregar slide')
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title']['es'] ?? null)
                                ->schema([
                                    MediaPicker::make('media_id')
                                        ->label('Imagen')
                                        ->tableConfiguration(MediaLibraryTable::class),
                                    Tabs::make('slide_idiomas')->tabs([
                                        Tab::make('Español')->schema([
                                            TextInput::make('title.es')->label('Título'),
                                            TextInput::make('subtitle.es')->label('Bajada'),
                                            TextInput::make('cta_label.es')->label('Texto del botón'),
                                        ]),
                                        Tab::make('Italiano')->schema([
                                            TextInput::make('title.it')->label('Título'),
                                            TextInput::make('subtitle.it')->label('Bajada'),
                                            TextInput::make('cta_label.it')->label('Texto del botón'),
                                        ])->visible(fn () => SiteSetting::italianEnabled()),
                                    ]),
                                    TextInput::make('cta_url')
                                        ->label('URL del botón')
                                        ->helperText('Ruta interna (ej. /admisiones) o URL completa (ej. https://...)')
                                        ->rule('regex:/^(https?:\/\/|\/).+$/'),
                                ]),
                        ]),

                    Section::make('Cifras')
                        ->description('"Más de un siglo de historia" — cada tarjeta con su valor, símbolo y descripción.')
                        ->schema([
                            Repeater::make('stats')
                                ->hiddenLabel()
                                ->reorderable()
                                ->addActionLabel('Agregar cifra')
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title']['es'] ?? null)
                                ->schema([
                                    TextInput::make('value')->label('Valor')->helperText('Ej: 129 o Afiliados')->required(),
                                    TextInput::make('symbol')->label('Símbolo (opcional)')->helperText('Ej: ° o +')->maxLength(5),
                                    Tabs::make('stat_idiomas')->tabs([
                                        Tab::make('Español')->schema([
                                            TextInput::make('title.es')->label('Título'),
                                            Textarea::make('description.es')->label('Descripción')->rows(2),
                                        ]),
                                        Tab::make('Italiano')->schema([
                                            TextInput::make('title.it')->label('Título'),
                                            Textarea::make('description.it')->label('Descripción')->rows(2),
                                        ])->visible(fn () => SiteSetting::italianEnabled()),
                                    ]),
                                ]),
                        ]),

                    Section::make('Orden y secciones del inicio')
                        ->description('Arrastrá para reordenar. Apagá una sección para ocultarla del inicio sin borrar su contenido.')
                        ->schema([
                            Repeater::make('sections')
                                ->hiddenLabel()
                                ->reorderable()
                                ->addable(false)
                                ->deletable(false)
                                ->itemLabel(fn (array $state): string => HomeSetting::SECTION_LABELS[$state['key'] ?? ''] ?? $state['key'] ?? '')
                                ->schema([
                                    TextInput::make('key')
                                        ->disabled()
                                        ->dehydrated()
                                        ->hiddenLabel()
                                        ->extraFieldWrapperAttributes(['style' => 'display:none']),
                                    Toggle::make('enabled')->label('Activada'),
                                ])
                                ->columns(1),
                        ]),

                    Section::make('Páginas destacadas')
                        ->description('El interruptor "Destacar en el inicio" vive en cada página — esto es solo un resumen de cuáles están activas ahora mismo.')
                        ->schema([
                            Text::make(function (): HtmlString {
                                $pages = PageModel::query()
                                    ->where('is_featured_home', true)
                                    ->orderBy('sort_order')
                                    ->get(['id', 'title', 'slug']);

                                if ($pages->isEmpty()) {
                                    return new HtmlString('<p>Ninguna página está destacada todavía.</p>');
                                }

                                $items = $pages->map(function (PageModel $page) {
                                    $url = route('filament.admin.resources.pages.edit', ['record' => $page]);

                                    return '<li><a href="'.e($url).'" style="text-decoration:underline">'.e($page->title).'</a></li>';
                                })->implode('');

                                return new HtmlString('<ul style="margin:0;padding-left:1.25rem">'.$items.'</ul>');
                            }),
                        ]),

                    Section::make('Llamado a la acción (CTA)')
                        ->schema([
                            Tabs::make('cta_idiomas')->tabs([
                                Tab::make('Español')->schema([
                                    TextInput::make('cta_title.es')->label('Título'),
                                    Textarea::make('cta_text.es')->label('Texto')->rows(2),
                                    TextInput::make('cta_button_label.es')->label('Texto del botón'),
                                ]),
                                Tab::make('Italiano')->schema([
                                    TextInput::make('cta_title.it')->label('Título'),
                                    Textarea::make('cta_text.it')->label('Texto')->rows(2),
                                    TextInput::make('cta_button_label.it')->label('Texto del botón'),
                                ])->visible(fn () => SiteSetting::italianEnabled()),
                            ]),
                            TextInput::make('cta_button_url')
                                ->label('URL del botón')
                                ->helperText('Ruta interna (ej. /admisiones) o URL completa (ej. https://...)')
                                ->rule('regex:/^(https?:\/\/|\/).+$/'),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Guardar')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->getRecord()->update($data);

        Notification::make()
            ->success()
            ->title('Guardado')
            ->send();
    }
}
