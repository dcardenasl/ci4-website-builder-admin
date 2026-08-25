<?php /** @var list<array<string, mixed>> $items */ ?>

<section class="mx-auto max-w-3xl space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-600"><?= esc(lang('Collections.collections_title')) ?></p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900"><?= esc(lang('Collections.collections_reorder')) ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= esc(lang('Collections.collections_reorder_help')) ?></p>
        </div>
        <a href="<?= esc(route_to('admin.cms.collections')) ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
    </div>

    <form method="post" action="<?= esc(route_to('admin.cms.collections.save_order')) ?>" x-data="collectionSortOrderEditor()" @submit="syncNames()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <?= csrf_field() ?>
        <ol class="space-y-2" x-ref="rows">
            <?php foreach ($items as $index => $item): ?>
                <li data-sort-row class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-3">
                    <span class="cursor-grab text-gray-400" aria-hidden="true">☷</span>
                    <input type="hidden" data-sort-id name="items[<?= $index ?>][id]" value="<?= esc((string) ($item['id'] ?? '')) ?>">
                    <span class="min-w-0 flex-1 font-medium text-gray-800"><?= esc((string) ($item['collection_key'] ?? $item['name'] ?? ('#' . ($item['id'] ?? '')))) ?></span>
                    <div class="flex items-center gap-1">
                        <button type="button" class="<?= esc(action_button_class()) ?>" @click="move($el.closest('[data-sort-row]'), -1)" aria-label="<?= esc(lang('Collections.collections_move_up')) ?>">↑</button>
                        <button type="button" class="<?= esc(action_button_class()) ?>" @click="move($el.closest('[data-sort-row]'), 1)" aria-label="<?= esc(lang('Collections.collections_move_down')) ?>">↓</button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
        <div class="mt-5 flex justify-end">
            <button type="submit" class="<?= esc(primary_button_class()) ?>"><?= esc(lang('Collections.collections_reorder_save')) ?></button>
        </div>
    </form>
</section>

<script>
function collectionSortOrderEditor() {
    return {
        move(row, direction) {
            if (!(row instanceof HTMLElement)) return;
            const sibling = direction < 0 ? row.previousElementSibling : row.nextElementSibling;
            if (!(sibling instanceof HTMLElement)) return;
            direction < 0 ? row.parentElement.insertBefore(row, sibling) : row.parentElement.insertBefore(sibling, row);
        },
        syncNames() {
            Array.from(this.$refs.rows.querySelectorAll('[data-sort-id]')).forEach((input, index) => {
                input.name = `items[${index}][id]`;
            });
        },
    };
}
</script>
