<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Services\Html\HtmlSanitizer;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
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
