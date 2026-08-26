{{--
    Registrado una sola vez por el panel entero vía `PanelsRenderHook::SCRIPTS_AFTER`
    (AdminPanelProvider) — a propósito, NO vive dentro de `livewire.manage-menu-items`:
    ese componente se vuelve a renderizar en cada acción (arrastrar, activar/desactivar,
    editar), y un <script> reinyectado así no es fiable para (re)inicializar Sortable ni
    para no duplicar los listeners de `livewire:init`.
--}}
<script>
    function danteMenuTreeInit() {
        document.querySelectorAll('[data-menu-sortable]').forEach((el) => {
            if (el._danteSortable || typeof window.Sortable === 'undefined') {
                return;
            }

            el._danteSortable = window.Sortable.create(el, {
                group: 'dante-menu-items',
                handle: '.menu-item-handle',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                ghostClass: 'fi-sortable-ghost',
                onEnd(event) {
                    danteMenuTreePersist(event.from);
                },
            });
        });
    }

    function danteMenuTreePersist(el) {
        const root = el.closest('[wire\\:id]');

        if (!root) {
            return;
        }

        const tree = [];

        root.querySelectorAll('[data-menu-sortable]').forEach((list) => {
            const parentAttr = list.dataset.parentId;
            const parentId = parentAttr ? parseInt(parentAttr, 10) : null;

            Array.from(list.children).forEach((li, index) => {
                if (!li.dataset.itemId) {
                    return;
                }

                tree.push({
                    id: parseInt(li.dataset.itemId, 10),
                    parent_id: parentId,
                    sort_order: index,
                });
            });
        });

        window.Livewire.find(root.getAttribute('wire:id')).call('updateOrder', tree);
    }

    document.addEventListener('livewire:init', () => {
        window.Livewire.hook('morph.updated', () => danteMenuTreeInit());
    });
    document.addEventListener('livewire:navigated', () => danteMenuTreeInit());
    document.addEventListener('DOMContentLoaded', () => danteMenuTreeInit());
</script>
