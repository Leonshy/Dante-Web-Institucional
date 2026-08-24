<?php

namespace App\View\Components\Blocks;

use App\Models\Document;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DocumentsList extends Component
{
    public Collection $documents;

    public function __construct(?int $categoryId = null)
    {
        $this->documents = Document::query()
            ->where('status', 'published')
            ->where('is_current', true)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->with('file')
            ->orderByDesc('published_at')
            ->get();
    }

    public function render(): View
    {
        return view('components.blocks.documentos');
    }
}
