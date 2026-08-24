<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\Concerns\SanitizesPageBlocks;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use SanitizesPageBlocks;

    protected static string $resource = PageResource::class;

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
        $data['updated_by'] = auth()->id();

        return $this->sanitizeBlocks($data);
    }
}
