<?php
$quality = is_array($quality ?? null) ? $quality : null;
if ($quality === null) {
    return;
}
$status = (string) ($quality['status'] ?? 'blocked');
if ($status === 'unavailable'):
    ?>
<section class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm" data-page-quality>
    <h3 class="text-lg font-semibold text-amber-900"><?= esc(lang('PageQuality.title')) ?></h3>
    <p class="mt-1 text-sm text-amber-800"><?= esc(lang('PageQuality.unavailable')) ?></p>
</section>
<?php return; endif;
$statusClass = match ($status) {
    'ready' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
    default => 'bg-red-50 text-red-700 ring-red-600/20',
};
$summary = is_array($quality['summary'] ?? null) ? $quality['summary'] : [];
$checks = array_values(array_filter(
    is_array($quality['checks'] ?? null) ? $quality['checks'] : [],
    static fn (mixed $check): bool => is_array($check) && in_array($check['status'] ?? '', ['fail', 'warning'], true),
));
?>
<section class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm" data-page-quality>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('PageQuality.title')) ?></h3>
            <p class="mt-0.5 text-xs text-gray-500"><?= esc(lang('PageQuality.description')) ?></p>
        </div>
        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset <?= esc($statusClass) ?>"><?= esc(lang('PageQuality.status_' . $status)) ?></span>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500"><?= esc(lang('PageQuality.score')) ?></p><p class="mt-1 text-xl font-bold text-gray-900"><?= esc((string) ($quality['score'] ?? 0)) ?>%</p></div>
        <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500"><?= esc(lang('PageQuality.errors')) ?></p><p class="mt-1 text-xl font-bold text-red-700"><?= esc((string) ($summary['errors'] ?? 0)) ?></p></div>
        <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500"><?= esc(lang('PageQuality.warnings')) ?></p><p class="mt-1 text-xl font-bold text-amber-700"><?= esc((string) ($summary['warnings'] ?? 0)) ?></p></div>
        <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500"><?= esc(lang('PageQuality.passed')) ?></p><p class="mt-1 text-xl font-bold text-emerald-700"><?= esc((string) ($summary['passed'] ?? 0)) ?></p></div>
    </div>
    <?php if ($checks !== []): ?>
        <ul class="mt-4 space-y-2 text-sm">
            <?php foreach ($checks as $check): ?>
                <?php $messageKey = preg_replace('/[^a-z0-9_]+/i', '', (string) ($check['message_key'] ?? 'unknown')); ?>
                <li class="flex items-start gap-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2">
                    <span class="mt-0.5 <?= ($check['status'] ?? '') === 'fail' ? 'text-red-600' : 'text-amber-600' ?>">●</span>
                    <?php $message = lang('PageQuality.check_' . $messageKey); ?>
                    <span class="text-gray-700"><?= esc($message === 'PageQuality.check_' . $messageKey ? lang('PageQuality.unknown_issue') : $message) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
