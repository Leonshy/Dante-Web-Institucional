<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Livewire\ManageMenuItems;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // El árbol arrastrable (App\Livewire\ManageMenuItems) reemplaza a la vieja
    // `ItemsRelationManager` — necesita ser un componente Livewire propio (no
    // una tabla de Filament) para poder anidar ítems por arrastre.
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
            Livewire::make(ManageMenuItems::class, ['record' => $this->getRecord()]),
        ]);
    }
}
