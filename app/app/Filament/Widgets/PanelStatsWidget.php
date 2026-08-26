<?php

namespace App\Filament\Widgets;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * "Estadísticas generales del panel" pedidas para el Escritorio (Fase 10) —
 * conteos reales de lo que un editor revisa seguido al entrar, no números
 * de ejemplo.
 */
class PanelStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Páginas publicadas', Page::query()->where('status', 'published')->count())
                ->icon('heroicon-o-document-duplicate'),
            Stat::make('Noticias publicadas', Post::query()->where('status', 'published')->count())
                ->icon('heroicon-o-newspaper'),
            Stat::make('Formularios recibidos', FormSubmission::query()->count())
                ->description('Últimos 30 días: '.FormSubmission::query()->where('created_at', '>=', now()->subDays(30))->count())
                ->icon('heroicon-o-inbox'),
            Stat::make('Documentos vigentes', Document::query()->where('is_current', true)->count())
                ->icon('heroicon-o-document-arrow-down'),
            Stat::make('Comunicados vigentes', Announcement::query()
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
                ->count())
                ->icon('heroicon-o-megaphone'),
        ];
    }
}
