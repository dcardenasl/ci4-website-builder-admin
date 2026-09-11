<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="<?= esc($currentLocale ?? 'es') ?>" data-env="<?= esc(ENVIRONMENT) ?>">
<head>
    <?= $this->include('layouts/partials/head') ?>
</head>
<?php /* Full-bleed: the canvas owns the viewport, so no sidebar and no page padding. */ ?>
<?php /* The shared modals bind x-show against Alpine stores; without an Alpine
         root on the body they never initialize and would render open. */ ?>
<body class="h-screen overflow-hidden bg-gray-100 font-sans text-gray-900" x-data="{}">
    <a href="#canvas-panel-preview" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-brand-700 focus:shadow">
        <?= lang('App.skip_to_content') ?>
    </a>
    <?= $this->include($view) ?>
    <?= $this->include('layouts/partials/confirm_modal') ?>
    <?= $this->include('layouts/partials/file_picker_modal') ?>
    <script <?= csp_script_nonce() ?> src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
