<?php

namespace App\View\Components\Blocks;

use App\Models\Gallery;
use App\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class GalleryBlock extends Component
{
    public Collection $images;

    public function __construct(public ?int $galleryId = null, public string $layout = 'grid')
    {
        $gallery = $galleryId ? Gallery::query()->where('status', 'published')->with('media')->find($galleryId) : null;

        $this->images = $gallery
            ? $gallery->media->map(fn (Media $media) => ['url' => $media->conversionUrl('medium') ?? $media->url(), 'alt' => $media->alt ?? ''])
            : collect();
    }

    public function render(): View
    {
        return view('components.blocks.galeria-block');
    }
}
