<?php

namespace App\Filament\Pages;

use App\Models\IntegrationSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * IDs y credenciales de integraciones (GA4/GTM, Meta Pixel + Conversions
 * API, Turnstile), cada una con su propio interruptor de activo/inactivo
 * (docs/08-seo.md §6) — antes vivían repartidas entre `.env` (secretos) y
 * `SiteSetting` (IDs). Pedido explícito del cliente: todo desde acá, nada
 * en un archivo que solo puede tocar quien tiene acceso al servidor.
 *
 * @property-read Schema $form
 */
class IntegrationSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $navigationLabel = 'Integraciones';

    protected static string|UnitEnum|null $navigationGroup = 'Configuraciones';

    protected static ?int $navigationSort = 16;

    protected static ?string $title = 'Integraciones';

    protected string $view = 'filament.pages.integration-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('viewAny', IntegrationSetting::class);
    }

    public function mount(): void
    {
        $this->form->fill($this->getRecord()->attributesToArray());
    }

    public function getRecord(): IntegrationSetting
    {
        return IntegrationSetting::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Google Analytics / Tag Manager')
                        ->description('El ID de GA4 se configura dentro del contenedor de GTM — acá queda solo de referencia.')
                        ->schema([
                            Toggle::make('ga_enabled')
                                ->label('Activo')
                                ->live()
                                ->helperText('Apagado: no se carga ningún script de Google, aunque los IDs sigan guardados.'),
                            TextInput::make('google_tag_manager_id')
                                ->label('ID de Google Tag Manager')
                                ->placeholder('GTM-XXXXXXX')
                                ->visible(fn ($get) => $get('ga_enabled')),
                            TextInput::make('google_analytics_id')
                                ->label('ID de Google Analytics (GA4, referencia)')
                                ->placeholder('G-XXXXXXXXXX')
                                ->visible(fn ($get) => $get('ga_enabled')),
                        ]),

                    Section::make('Meta Pixel + Conversions API')
                        ->description('El Pixel corre en el navegador; Conversions API es el envío del lado servidor — sobrevive a los bloqueadores de contenido.')
                        ->schema([
                            Toggle::make('meta_enabled')
                                ->label('Activo')
                                ->live()
                                ->helperText('Apagado: no se carga el Pixel ni se envían eventos a Conversions API.'),
                            TextInput::make('meta_pixel_id')
                                ->label('ID de Meta Pixel')
                                ->visible(fn ($get) => $get('meta_enabled')),
                            TextInput::make('meta_capi_access_token')
                                ->label('Token de acceso — Conversions API')
                                ->password()
                                ->revealable()
                                ->visible(fn ($get) => $get('meta_enabled'))
                                ->helperText('Se guarda cifrado. Se genera en Meta Business Suite → Conversions API.'),
                            TextInput::make('meta_capi_test_event_code')
                                ->label('Código de evento de prueba (opcional)')
                                ->visible(fn ($get) => $get('meta_enabled'))
                                ->helperText('Solo mientras se verifica en el Test Events de Meta — quitarlo antes de producción.'),
                        ]),

                    Section::make('Cloudflare Turnstile (captcha)')
                        ->description('Verificación anti-robots en los formularios públicos, sumada al honeypot y al límite de envíos.')
                        ->schema([
                            Toggle::make('turnstile_enabled')
                                ->label('Activo')
                                ->live()
                                ->helperText('Apagado: los formularios no piden verificación anti-robots (solo honeypot y límite de envíos).'),
                            TextInput::make('turnstile_site_key')
                                ->label('Site key')
                                ->visible(fn ($get) => $get('turnstile_enabled')),
                            TextInput::make('turnstile_secret_key')
                                ->label('Secret key')
                                ->password()
                                ->revealable()
                                ->visible(fn ($get) => $get('turnstile_enabled'))
                                ->helperText('Se guarda cifrada. Se genera en el panel de Cloudflare → Turnstile.'),
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
