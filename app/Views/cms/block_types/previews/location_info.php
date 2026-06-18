<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$addressLabel = esc($data['address_label'] ?? 'Address');
$address      = esc($data['address'] ?? '');
$phoneLabel   = esc($data['phone_label'] ?? 'Phone');
$phone        = esc($data['phone'] ?? '');
$hoursLabel   = esc($data['hours_label'] ?? 'Office Hours');
$hours        = esc($data['hours'] ?? '');
$mapEmbedUrl  = $config['map_embed_url'] ?? '';
$cssClass     = esc($config['css_class'] ?? '');
?>
<section class="py-10 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Contact details -->
            <div class="space-y-6">
                <?php if ($address): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($addressLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $addressLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5"><?= $address ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($phone): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($phoneLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $phoneLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5"><?= $phone ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hours): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <?php if ($hoursLabel): ?>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= $hoursLabel ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-800 mt-0.5 whitespace-pre-line"><?= $hours ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (! $address && ! $phone && ! $hours): ?>
                    <div class="p-4 bg-gray-50 border border-dashed border-gray-200 rounded-lg text-sm text-gray-400 text-center">
                        Configure address, phone, and hours in the form.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map -->
            <div class="rounded-xl overflow-hidden bg-gray-100 h-56 flex items-center justify-center">
                <?php if ($mapEmbedUrl): ?>
                    <iframe src="<?= esc($mapEmbedUrl) ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                <?php else: ?>
                    <div class="text-center text-gray-400 p-4">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/>
                        </svg>
                        <p class="text-xs">Enter Google Maps embed URL in the configuration</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
