<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use App\Models\Gallery;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    /** @var array<int, int> */
    private array $pendingMediaIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingMediaIds = array_values(array_filter((array) ($data['media'] ?? [])));
        unset($data['media']);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if (! $record instanceof Gallery) {
            return;
        }

        $record->media()->sync(
            collect($this->pendingMediaIds)
                ->values()
                ->mapWithKeys(fn (int $mediaId, int $index) => [$mediaId => ['sort_order' => $index]])
                ->all(),
        );
    }
}
