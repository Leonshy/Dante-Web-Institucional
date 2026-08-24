<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Services\Html\HtmlSanitizer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

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

        if (isset($data['content']) && is_array($data['content'])) {
            $sanitizer = app(HtmlSanitizer::class);
            foreach ($data['content'] as $locale => $html) {
                $data['content'][$locale] = $sanitizer->clean($html);
            }
        }

        return $data;
    }
}
