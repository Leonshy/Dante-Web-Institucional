<?php

namespace App\Filament\Forms\Components;

use App\Models\Media;
use App\Services\Media\MediaUploadService;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TableSelect;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Campo de imagen/archivo a medida: miniatura clickeable como disparador
 * (no un botón de texto aparte) que abre un modal con dos pestañas —
 * "Biblioteca" (grid de `MediaLibraryTable`, buscador y filtro) y "Subir"
 * (subida directa, con recorte de imagen) — replicando el picker real de
 * IGP (`media-picker-modal.blade.php` + `admin.js`). Sin pestaña "URL": ni
 * IGP la tiene, ni el cliente la pidió al confirmarlo explícitamente.
 *
 * `ModalTableSelect` (el componente nativo de Filament) no sirve para esto:
 * su modal tiene pie fijo sin pestañas, no admite un disparador propio, y
 * `TableSelectLivewireComponent` vacía `headerActions()`/`recordActions()`
 * de la tabla (confirmado leyendo el vendor), así que no hay forma de meter
 * "Subir" adentro de su modal. Por eso este campo usa `TableSelect` (la
 * pieza más chica, sin el modal envolvente) directamente dentro de la
 * pestaña "Biblioteca" de un `Action` propio.
 */
class MediaPicker extends Field
{
    protected string $view = 'filament.forms.components.media-picker';

    protected string $tableConfiguration;

    protected bool $anyFileType = false;

    protected bool $isMultiple = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (self $component): Action => $component->getChooseAction(),
            fn (self $component): Action => $component->getRemoveAction(),
        ]);
    }

    public function tableConfiguration(string $tableConfiguration): static
    {
        $this->tableConfiguration = $tableConfiguration;

        return $this;
    }

    public function anyFileType(bool $condition = true): static
    {
        $this->anyFileType = $condition;

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    public function getChooseAction(): Action
    {
        $action = Action::make('choose')
            ->modalHeading($this->getLabel())
            ->modalSubmitActionLabel('Elegir')
            ->fillForm(fn (): array => ['selection' => $this->isMultiple() ? [] : $this->getState()])
            ->schema([
                Tabs::make('media_picker_tabs')->tabs([
                    Tab::make('Biblioteca')->schema([
                        TableSelect::make('selection')
                            ->hiddenLabel()
                            ->model(Media::class)
                            ->multiple($this->isMultiple())
                            ->tableConfiguration($this->tableConfiguration),
                    ]),
                    Tab::make('Subir')->schema([
                        FileUpload::make('upload')
                            ->label('Archivo')
                            ->when(! $this->anyFileType, fn (FileUpload $upload) => $upload->image()->imageEditor())
                            ->acceptedFileTypes(config('dante.media.allowed_mimes'))
                            ->maxSize(config('dante.media.max_upload_kb'))
                            ->storeFiles(false),
                        TextInput::make('alt')
                            ->label('Texto alternativo')
                            ->helperText('Describí qué muestra la imagen — lo necesitan las personas que usan lector de pantalla y ayuda al posicionamiento en Google.')
                            ->requiredWith('upload')
                            ->maxLength(255),
                    ]),
                ]),
            ])
            ->action(function (array $data): void {
                $upload = $data['upload'] ?? null;
                $selection = $data['selection'] ?? null;

                $newMediaId = null;

                if ($upload instanceof TemporaryUploadedFile) {
                    $newMediaId = app(MediaUploadService::class)->upload($upload, 'general', $data['alt'] ?? null)->id;
                }

                if ($this->isMultiple()) {
                    $current = Collection::make((array) ($this->getState() ?? []))->filter()->values();
                    $picked = Collection::make((array) ($selection ?? []))->filter();

                    $merged = $current->merge($picked)
                        ->when($newMediaId, fn (Collection $ids) => $ids->push($newMediaId))
                        ->unique()
                        ->values()
                        ->all();

                    $this->state($merged);
                    $this->callAfterStateUpdated();

                    return;
                }

                if ($newMediaId) {
                    $this->state($newMediaId);
                    $this->callAfterStateUpdated();

                    return;
                }

                if (filled($selection)) {
                    $this->state($selection);
                    $this->callAfterStateUpdated();
                }
            });

        if ($this->isMultiple()) {
            return $action->label('Agregar imágenes')->icon('heroicon-o-plus');
        }

        return $action
            ->label(function (): HtmlString {
                $selected = $this->selectedMediaItems()->first();

                return new HtmlString($selected ? $this->thumbnailHtml($selected) : $this->placeholderHtml());
            })
            ->link()
            ->extraAttributes(['class' => 'dante-media-picker-trigger']);
    }

    public function getRemoveAction(): Action
    {
        return Action::make('remove')
            ->label('Quitar')
            ->color('danger')
            ->link()
            ->visible(fn (): bool => (! $this->isMultiple()) && filled($this->getState()))
            ->action(function (): void {
                $this->state(null);
                $this->callAfterStateUpdated();
            });
    }

    protected function thumbnailHtml(Media $media): string
    {
        if ($media->type !== 'image') {
            return '<span class="dante-media-picker-file">'.e($media->name).'</span>';
        }

        $url = $media->conversionUrl('small') ?? $media->url();

        return '<img src="'.e($url).'" alt="" class="dante-media-picker-img">';
    }

    protected function placeholderHtml(): string
    {
        return '<span class="dante-media-picker-placeholder">'.e($this->anyFileType ? 'Elegir archivo' : 'Elegir imagen').'</span>';
    }

    /**
     * @return Collection<int, Media>
     */
    public function selectedMediaItems(): Collection
    {
        $ids = $this->isMultiple() ? (array) ($this->getState() ?? []) : array_filter([$this->getState()]);

        if (empty($ids)) {
            return Collection::make();
        }

        $mediaById = Media::query()->whereIn('id', $ids)->get()->keyBy('id');

        return Collection::make($ids)->map(fn ($id) => $mediaById->get($id))->filter()->values();
    }
}
