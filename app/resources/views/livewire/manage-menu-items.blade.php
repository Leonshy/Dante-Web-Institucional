<div wire:key="manage-menu-items-{{ $record->id }}">
    <style>
        .menu-tree-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .menu-tree-header h2 { font-size: 1rem; font-weight: 700; }
        .menu-tree-header button { border: 1px solid currentColor; border-radius: .5rem; padding: .375rem .75rem; font-size: .8125rem; }
        .menu-tree-root, .menu-tree-children { list-style: none; margin: 0; padding: 0; }
        .menu-tree-children { margin-top: .5rem; margin-left: 2rem; min-height: .5rem; }
        .menu-tree-item { border: 1px solid rgba(120, 120, 120, .25); border-radius: .5rem; margin-bottom: .5rem; background: rgba(120, 120, 120, .03); }
        .menu-tree-row { display: flex; align-items: center; gap: .625rem; padding: .5rem .75rem; flex-wrap: wrap; }
        .menu-item-handle { cursor: grab; opacity: .5; display: inline-flex; }
        .menu-item-handle:hover { opacity: 1; }
        .menu-tree-label { font-weight: 600; }
        .menu-tree-url { opacity: .6; font-size: .8125rem; }
        .menu-tree-actions { display: flex; align-items: center; gap: .75rem; margin-left: auto; }
        .menu-tree-actions button { font-size: .75rem; text-decoration: underline; background: none; border: none; padding: 0; }
        .menu-tree-toggle { font-size: .75rem; border-radius: 999px; padding: .125rem .625rem; border: 1px solid rgba(120, 120, 120, .35); }
        .menu-tree-toggle.is-active { background: #dcfce7; border-color: #86efac; color: #166534; }
        .menu-tree-children:empty::before { content: 'Soltá acá un enlace para convertirlo en submenú'; display: block; padding: .5rem; font-size: .75rem; opacity: .5; }
        .fi-sortable-ghost { opacity: .4; }
    </style>

    <div class="menu-tree-header">
        <h2>Enlaces del menú</h2>
        <button type="button" wire:click="mountAction('create')">+ Agregar enlace</button>
    </div>

    <p style="font-size:.8125rem;opacity:.7;margin-bottom:.75rem">
        Arrastrá del ícono de puntos para reordenar. Soltá un enlace dentro de otro para que aparezca como su submenú.
    </p>

    <ul class="menu-tree-root" data-menu-sortable data-parent-id="">
        @foreach ($this->items->get(null, collect()) as $item)
            @include('livewire.partials.menu-item-node', ['item' => $item, 'children' => $this->items->get($item->id, collect())])
        @endforeach
    </ul>

    <x-filament-actions::modals />
</div>
