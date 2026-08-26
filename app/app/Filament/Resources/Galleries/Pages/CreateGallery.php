<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use App\Models\Gallery;
use Filament\Resources\Pages\CreateRecord;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    /** @var array<int, int> */
    private array $pendingMediaIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        $this->pendingMediaIds = array_values(array_filter((array) ($data['media'] ?? [])));
        unset($data['media']);

        return $data;
    }

    protected function afterCreate(): void
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
