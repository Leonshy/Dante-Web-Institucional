<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\Concerns\SanitizesPageBlocks;
use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    use SanitizesPageBlocks;

    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $this->sanitizeBlocks($data);
    }
}
