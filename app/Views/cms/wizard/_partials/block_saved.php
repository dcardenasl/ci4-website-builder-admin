<?php /* Wizard — B4: Block save confirmation */ ?>

<!-- ── SCREEN: BLOCK SAVE SUCCESS (B4) ── -->
<div x-show="screen === 'block-saved'" x-cloak class="text-center py-10">
    <div class="text-5xl mb-3">✅</div>
    <h2 class="text-xl font-bold mb-2"><?= lang('Wizard.block_saved_title') ?></h2>
    <p class="text-gray-500 text-sm mb-6"><?= lang('Wizard.block_saved_subtitle') ?></p>
    <div class="flex flex-col gap-3 items-center">
        <button @click="screen = 'page-blocks'" class="btn-primary"><?= lang('Wizard.btn_view_blocks') ?></button>
        <button @click="screen = 'home'" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></button>
    </div>
</div>
