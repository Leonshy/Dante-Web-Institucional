@php
    $isRoot = is_null($item->parent_id);
@endphp
<li data-item-id="{{ $item->id }}" wire:key="menu-item-{{ $item->id }}" class="menu-tree-item">
    <div class="menu-tree-row">
        <span class="menu-item-handle" title="Arrastrar para reordenar / anidar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" />
                <circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" />
                <circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" />
            </svg>
        </span>

        <span class="menu-tree-label">{{ $item->label }}</span>
        <span class="menu-tree-url">{{ $item->resolvedUrl() }}</span>

        <button
            type="button"
            wire:click="toggleActive({{ $item->id }})"
            class="menu-tree-toggle{{ $item->is_active ? ' is-active' : '' }}"
        >
            {{ $item->is_active ? 'Activo' : 'Inactivo' }}
        </button>

        <span class="menu-tree-actions">
            @if ($isRoot)
                <button type="button" wire:click="mountAction('create', { parent_id: {{ $item->id }} })">
                    + Submenú
                </button>
            @endif

            <button type="button" wire:click="mountAction('edit', { item: {{ $item->id }} })">Editar</button>
            <button type="button" wire:click="mountAction('delete', { item: {{ $item->id }} })">Borrar</button>
        </span>
    </div>

    @if ($isRoot)
        <ul class="menu-tree-children" data-menu-sortable data-parent-id="{{ $item->id }}">
            @foreach ($children as $child)
                @include('livewire.partials.menu-item-node', ['item' => $child, 'children' => collect()])
            @endforeach
        </ul>
    @endif
</li>
