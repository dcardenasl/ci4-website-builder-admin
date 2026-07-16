<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$slides = [];
for ($i = 1; $i <= 3; $i++) {
    $prefix = "slide_{$i}";
    $image = is_array($data["{$prefix}_image"] ?? null) ? $data["{$prefix}_image"] : [];
    if (! empty($image['url'])) {
        $slides[] = [
            'image'          => $image,
            'image_alt_text' => (string) ($data["{$prefix}_heading"] ?? ''),
            'heading'        => (string) ($data["{$prefix}_heading"] ?? ''),
            'subtitle'       => (string) ($data["{$prefix}_subtitle"] ?? ''),
            'cta_label'      => (string) ($data["{$prefix}_cta_label"] ?? ''),
            'cta_url'        => (string) ($data["{$prefix}_cta_url"] ?? '#'),
        ];
    }
}

if ($slides === []) {
    $slides[] = [
        'image'          => [
            'source_kind' => 'external_url',
            'file_id'     => null,
            'url'         => 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221200%22%20height%3D%22500%22%20viewBox%3D%220%200%201200%20500%22%3E%3Crect%20width%3D%221200%22%20height%3D%22500%22%20fill%3D%22%23e5e7eb%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23111827%22%20font-family%3D%22Arial%2CHelvetica%2Csans-serif%22%20font-size%3D%2256%22%20font-weight%3D%22700%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3ECarrusel%20Hero%3C%2Ftext%3E%3C%2Fsvg%3E',
        ],
        'image_alt_text' => 'Carrusel Hero',
        'heading'        => 'Carrusel Hero',
        'subtitle'       => 'Texto y controles configurables desde el dominio.',
        'cta_label'      => 'Ver más',
        'cta_url'        => '#',
    ];
}

$captionPosition = (string) ($config['caption_position'] ?? 'below');
if (! in_array($captionPosition, ['below', 'overlay_top', 'overlay_bottom', 'hide'], true)) {
    $captionPosition = 'below';
}

$controlsPosition = (string) ($config['controls_position'] ?? 'below');
if (! in_array($controlsPosition, ['below', 'overlay_bottom'], true)) {
    $controlsPosition = 'below';
}

$overlayOpacity = isset($config['overlay_opacity']) ? max(0, min(80, (int) $config['overlay_opacity'])) : 0;
$cssClass = esc($config['css_class'] ?? '');
$first = $slides[0];
$slideCount = count($slides);

$buildCaptionCard = static function (array $slide) use ($captionPosition): string {
    $title = esc($slide['heading'] ?? '');
    $subtitle = esc($slide['subtitle'] ?? '');
    $ctaLabel = esc($slide['cta_label'] ?? '');
    $isOverlay = in_array($captionPosition, ['overlay_top', 'overlay_bottom'], true);

    $cardClasses = $isOverlay
        ? 'rounded-2xl bg-slate-950/65 px-4 py-3 text-white shadow-2xl shadow-slate-950/20 ring-1 ring-white/10 backdrop-blur-md'
        : 'px-0 py-0 text-slate-900';

    ob_start();
    ?>
    <div class="<?= $cardClasses ?>">
        <?php if ($title !== ''): ?>
            <h2 data-hero-caption-title class="text-xl font-semibold tracking-tight sm:text-[1.6rem]"><?= $title ?></h2>
        <?php endif; ?>
        <?php if ($subtitle !== ''): ?>
            <p data-hero-caption-subtitle class="mt-1 max-w-2xl text-sm leading-relaxed <?= $isOverlay ? 'text-white/85' : 'text-slate-600' ?> sm:text-[0.98rem]">
                <?= $subtitle ?>
            </p>
        <?php endif; ?>
        <?php if ($ctaLabel !== ''): ?>
            <span data-hero-caption-cta class="mt-2 inline-flex items-center text-[11px] font-semibold uppercase tracking-[0.14em]">
                <?= $ctaLabel ?>
            </span>
        <?php endif; ?>
    </div>
    <?php

    return (string) ob_get_clean();
};

$buildControls = static function (int $total): string {
    ob_start();
    ?>
    <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-2 py-1.5">
        <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700" aria-label="Anterior">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div class="flex items-center gap-2" aria-label="Diapositivas">
            <?php for ($i = 0; $i < $total; $i++): ?>
                <button
                    type="button"
                    class="flex h-2 w-2 items-stretch overflow-hidden rounded-full border border-slate-300 <?= $i === 0 ? 'bg-slate-100' : 'bg-slate-200' ?>"
                    aria-label="Ir a la diapositiva <?= $i + 1 ?>"
                    aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                >
                    <span class="block h-full w-full bg-slate-900 <?= $i === 0 ? '' : 'scale-x-0' ?>" style="transform-origin:left center;"></span>
                </button>
            <?php endfor; ?>
        </div>
        <button type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700" aria-label="Siguiente">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
    <?php

    return (string) ob_get_clean();
};
?>

<div class="<?= $cssClass ?> space-y-4" data-caption-position="<?= esc($captionPosition, 'attr') ?>" data-controls-position="<?= esc($controlsPosition, 'attr') ?>">
    <div
        class="relative overflow-hidden rounded-2xl bg-slate-100"
        style="aspect-ratio: 12 / 5;"
    >
        <img
            src="<?= esc($first['image']['url']) ?>"
            alt="<?= esc($first['image_alt_text'] ?? $first['heading'] ?? '') ?>"
            class="absolute inset-0 h-full w-full object-cover object-center"
        />
        <div
            class="absolute inset-0"
            style="background:linear-gradient(to bottom, rgba(15, 23, 42, <?= number_format($overlayOpacity / 100, 2, '.', '') ?>) 0%, rgba(15, 23, 42, 0) 42%, rgba(15, 23, 42, <?= number_format($overlayOpacity / 100, 2, '.', '') ?>) 100%);"
        ></div>

        <?php if ($captionPosition === 'overlay_top'): ?>
            <div class="absolute inset-x-0 top-0 z-20 p-4 sm:p-6">
                <div class="max-w-2xl">
                    <?= $buildCaptionCard($first) ?>
                </div>
            </div>
        <?php elseif ($captionPosition === 'overlay_bottom'): ?>
            <div class="absolute inset-x-0 bottom-0 z-20 p-4 sm:p-6">
                <div class="max-w-2xl">
                    <?= $buildCaptionCard($first) ?>
                </div>
            </div>
        <?php endif; ?>

        <a
            href="<?= esc($first['cta_url'] ?? '#') ?>"
            class="absolute inset-0 z-10 block"
            aria-label="<?= esc($first['heading'] ?? '') ?>"
        ></a>

        <?php if ($controlsPosition === 'overlay_bottom'): ?>
            <div class="absolute inset-x-0 bottom-4 z-30 flex justify-center px-4">
                <?= $buildControls($slideCount) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($captionPosition === 'below'): ?>
        <div class="max-w-4xl">
            <?= $buildCaptionCard($first) ?>
        </div>
    <?php endif; ?>

    <?php if ($controlsPosition === 'below'): ?>
        <div class="flex justify-center">
            <?= $buildControls($slideCount) ?>
        </div>
    <?php endif; ?>
</div>
