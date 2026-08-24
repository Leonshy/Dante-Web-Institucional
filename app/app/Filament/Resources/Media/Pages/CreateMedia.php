<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Services\Media\MediaUploadService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var TemporaryUploadedFile $upload */
        $upload = $data['upload'];

        $media = app(MediaUploadService::class)->upload(
            $upload,
            $data['folder'] ?? 'general',
            $data['alt'] ?? null,
        );

        $media->update([
            'title' => $data['title'] ?? null,
        ]);

        return $media;
    }
}
