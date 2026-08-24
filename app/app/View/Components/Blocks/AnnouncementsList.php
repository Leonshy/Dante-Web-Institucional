<?php

namespace App\View\Components\Blocks;

use App\Models\Announcement;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class AnnouncementsList extends Component
{
    public Collection $announcements;

    public function __construct(int $count = 5)
    {
        $this->announcements = Announcement::query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit($count)
            ->get();
    }

    public function render(): View
    {
        return view('components.blocks.listado-comunicados');
    }
}
