<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="<?= esc($currentLocale ?? 'es') ?>" data-env="<?= esc(ENVIRONMENT) ?>">
<head><?= $this->include('layouts/partials/head') ?></head>
<body class="h-screen overflow-hidden bg-gray-100 font-sans text-gray-900" x-data="{}">
    <a href="#editor-canvas" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-brand-700 focus:shadow"><?= lang('App.skip_to_content') ?></a>
    <?= $this->include($view) ?>
    <?= $this->include('layouts/partials/confirm_modal') ?>
    <?= $this->include('layouts/partials/file_picker_modal') ?>
    <script <?= csp_script_nonce() ?> src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
