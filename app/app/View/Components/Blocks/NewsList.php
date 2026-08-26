<?php

namespace App\View\Components\Blocks;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Bloque "listado_noticias" del constructor de páginas (PageBlocks::class).
 * La consulta vive acá, no en la vista (CLAUDE.md §7).
 */
class NewsList extends Component
{
    public Collection $posts;

    public function __construct(int $count = 3)
    {
        $this->posts = Post::query()
            ->with(['category', 'featuredMedia'])
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit($count)
            ->get();
    }

    public function render(): View
    {
        return view('components.blocks.listado-noticias');
    }
}
