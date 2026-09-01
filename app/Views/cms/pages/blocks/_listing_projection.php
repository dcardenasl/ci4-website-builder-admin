<?php

$listingFieldCatalog = is_array($listingFieldCatalog ?? null) ? $listingFieldCatalog : [];
$blockConfig = is_array($blockConfig ?? null) ? $blockConfig : [];
$submittedBlockConfig = is_array($submittedBlockConfig ?? null) ? $submittedBlockConfig : $blockConfig;
$rawProjection = $submittedBlockConfig['listing_projection'] ?? ($blockConfig['listing_projection'] ?? []);
if (is_string($rawProjection)) {
    $rawProjection = json_decode($rawProjection, true);
}
$projection = is_array($rawProjection) ? $rawProjection : [];
$projection['slots'] = is_array($projection['slots'] ?? null) ? $projection['slots'] : [];
$projection['order'] = is_array($projection['order'] ?? null) ? $projection['order'] : [];
$collection = (string) ($submittedBlockConfig['collection_id'] ?? $submittedBlockConfig['collection_key'] ?? $blockConfig['collection_id'] ?? $blockConfig['collection_key'] ?? '');
$legacyDate = trim((string) ($submittedBlockConfig['date_field'] ?? $blockConfig['date_field'] ?? ''));
$legacyOrder = trim((string) ($submittedBlockConfig['order_by'] ?? $blockConfig['order_by'] ?? ''));
$catalogFields = [];
foreach ($listingFieldCatalog as $fields) {
    if (is_array($fields)) {
        foreach ($fields as $field) {
            if (is_array($field)) {
                $catalogFields[] = $field;
            }
        }
    }
}
$legacyReference = static function (string $value) use ($catalogFields): string {
    if ($value === '' || $value === 'auto') {
        return '';
    }
    $value = str_starts_with($value, 'listing.') ? substr($value, 8) : (str_starts_with($value, 'field:') ? substr($value, 6) : $value);
    foreach ($catalogFields as $field) {
        $reference = (string) ($field['value'] ?? '');
        if ($reference !== '' && str_ends_with($reference, '.' . $value)) {
            return $reference;
        }
    }
    return '';
};
if (trim((string) ($projection['slots']['date'] ?? '')) === '') {
    $projection['slots']['date'] = $legacyReference($legacyDate);
}
if (trim((string) ($projection['order']['field'] ?? '')) === '') {
    $projection['order']['field'] = $legacyReference($legacyOrder);
}
$catalogJson = json_encode($listingFieldCatalog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
$projectionJson = json_encode($projection, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
$collectionJson = json_encode($collection, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '""';
$labelsJson = json_encode([
    'slot_title' => lang('Pages.listing_projection_slot_title'), 'slot_title_hint' => lang('Pages.listing_projection_slot_title_hint'),
    'slot_subtitle' => lang('Pages.listing_projection_slot_subtitle'), 'slot_subtitle_hint' => lang('Pages.listing_projection_slot_subtitle_hint'),
    'slot_summary' => lang('Pages.listing_projection_slot_summary'), 'slot_summary_hint' => lang('Pages.listing_projection_slot_summary_hint'),
    'slot_date' => lang('Pages.listing_projection_slot_date'), 'slot_date_hint' => lang('Pages.listing_projection_slot_date_hint'),
    'slot_image' => lang('Pages.listing_projection_slot_image'), 'slot_image_hint' => lang('Pages.listing_projection_slot_image_hint'),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?: '{}';
?>
<div class="border-t border-gray-100 pt-5"
     x-data="listingProjectionEditor(<?= esc($catalogJson, 'attr') ?>, <?= esc($projectionJson, 'attr') ?>, <?= esc($collectionJson, 'attr') ?>, <?= esc($labelsJson, 'attr') ?>)"
     @listing-projection-collection.window="syncContext($event.detail.value)">
    <input id="listing-projection-input" type="hidden" name="block_config[listing_projection]" :value="serialized()">
    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 space-y-5">
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-700"><i data-lucide="sliders-horizontal" class="h-4 w-4"></i></div>
            <div><h4 class="text-sm font-semibold text-slate-900"><?= esc(lang('Pages.listing_projection_title')) ?></h4><p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-500"><?= esc(lang('Pages.listing_projection_description')) ?></p></div>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
            <template x-for="slot in slots" :key="slot.key">
                <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm"><span class="flex items-center justify-between gap-2 text-xs font-semibold text-slate-700"><span x-text="slot.label"></span><span class="text-[10px] font-normal uppercase tracking-wider text-slate-400" x-text="slot.hint"></span></span>
                    <select class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" :value="projection.slots[slot.key] || ''" @change="setSlot(slot.key, $event.target.value)"><option value=""><?= esc(lang('Pages.listing_projection_option_none')) ?></option><template x-for="field in availableFields({ types: slot.types })" :key="field.value"><option :value="field.value" :selected="projection.slots[slot.key] === field.value" x-text="field.label"></option></template></select>
                </label>
            </template>
        </div>
        <div class="grid gap-4 border-t border-slate-200 pt-4 lg:grid-cols-2"><label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm"><span class="text-xs font-semibold text-slate-700"><?= esc(lang('Pages.listing_projection_order_field')) ?></span><select class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" :value="projection.order.field" @change="projection.order.field = $event.target.value"><option value=""><?= esc(lang('Pages.listing_projection_order_default')) ?></option><template x-for="field in availableFields({ sortable: true })" :key="field.value"><option :value="field.value" :selected="projection.order.field === field.value" x-text="field.label"></option></template></select></label><label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm"><span class="text-xs font-semibold text-slate-700"><?= esc(lang('Pages.listing_projection_order_direction')) ?></span><select class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm" x-model="projection.order.direction"><option value="asc"><?= esc(lang('Pages.listing_projection_direction_asc')) ?></option><option value="desc"><?= esc(lang('Pages.listing_projection_direction_desc')) ?></option></select></label></div>
        <label class="flex items-start gap-3 rounded-xl border border-brand-100 bg-brand-50/60 p-3"><input type="checkbox" class="mt-0.5 rounded border-slate-300 text-brand-600" :checked="projection.order.public === true" @change="projection.order.public = $event.target.checked"><span><span class="block text-xs font-semibold text-slate-800"><?= esc(lang('Pages.listing_projection_public_order_title')) ?></span><span class="mt-1 block text-[11px] leading-relaxed text-slate-600"><?= esc(lang('Pages.listing_projection_public_order_description')) ?></span></span></label>
        <div class="border-t border-slate-200 pt-4"><div class="flex items-center justify-between gap-3"><div><h5 class="text-xs font-semibold text-slate-800"><?= esc(lang('Pages.listing_projection_extras_title')) ?></h5><p class="mt-1 text-[11px] text-slate-500"><?= esc(lang('Pages.listing_projection_extras_description')) ?></p></div><button type="button" @click="addExtra()" class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-white px-3 py-2 text-xs font-semibold text-brand-700"><span aria-hidden="true">+</span> <?= esc(lang('Pages.listing_projection_add_extra')) ?></button></div><div class="mt-3 space-y-2"><template x-for="(item, index) in projection.extras" :key="item.id"><div class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:flex-row sm:items-center"><select class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm" x-model="item.source"><option value=""><?= esc(lang('Pages.listing_projection_option_select')) ?></option><template x-for="field in availableFields({ types: ['text', 'date', 'number', 'taxonomy', 'string', 'select'] })" :key="field.value"><option :value="field.value" x-text="field.label"></option></template></select><input type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm sm:w-44" placeholder="<?= esc(lang('Pages.listing_projection_extra_label_placeholder'), 'attr') ?>" x-model="item.label"><button type="button" @click="removeExtra(index)" class="rounded-lg px-2 py-2 text-xs font-medium text-red-600"><?= esc(lang('Pages.listing_projection_remove')) ?></button></div></template><p x-show="projection.extras.length === 0" class="rounded-xl border border-dashed border-slate-300 px-4 py-3 text-xs text-slate-500"><?= esc(lang('Pages.listing_projection_extras_empty')) ?></p></div></div>
    </div>
</div>
