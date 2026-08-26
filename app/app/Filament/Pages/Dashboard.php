<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PanelStatsWidget;
use App\Filament\Widgets\QuickLinksWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Reemplaza el Dashboard genérico de Filament (marca + versión) por
 * estadísticas reales del panel y accesos directos a las tareas más
 * frecuentes — pedido del cliente (Fase 10).
 */
class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Escritorio';

    protected static string|UnitEnum|null $navigationGroup = 'General';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Escritorio';

    public function getWidgets(): array
    {
        return [
            PanelStatsWidget::class,
            QuickLinksWidget::class,
        ];
    }
}
