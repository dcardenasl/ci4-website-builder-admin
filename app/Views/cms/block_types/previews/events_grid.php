<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$sectionTitle    = esc($data['section_title'] ?? 'Upcoming Events');
$sectionSubtitle = esc($data['section_subtitle'] ?? '');
$viewAllLabel    = esc($data['view_all_label'] ?? 'View all events');
$viewAllUrl      = esc($data['view_all_url'] ?? '#');
$collectionKey   = esc($config['collection_key'] ?? 'events');
$cssClass        = esc($config['css_class'] ?? '');

$sampleEvents = [
    ['title' => 'Summer Concert Series', 'date' => '15 Jul 2025', 'category' => 'Music', 'image' => 'https://placehold.co/400x260/1a1a2e/ffffff?text=Event+1'],
    ['title' => 'Theater Production: A Comedy', 'date' => '22 Jul 2025', 'category' => 'Theater', 'image' => 'https://placehold.co/400x260/16213e/ffffff?text=Event+2'],
    ['title' => 'Dance Performance', 'date' => '29 Jul 2025', 'category' => 'Dance', 'image' => 'https://placehold.co/400x260/0f3460/ffffff?text=Event+3'],
    ['title' => 'Jazz Night', 'date' => '5 Aug 2025', 'category' => 'Music', 'image' => 'https://placehold.co/400x260/533483/ffffff?text=Event+4'],
    ['title' => 'Art Exhibition', 'date' => '10 Aug 2025', 'category' => 'Exhibition', 'image' => 'https://placehold.co/400x260/2d6a4f/ffffff?text=Event+5'],
    ['title' => 'Family Show', 'date' => '17 Aug 2025', 'category' => 'Theater', 'image' => 'https://placehold.co/400x260/d62828/ffffff?text=Event+6'],
];
?>
<section class="py-10 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?= $sectionTitle ?></h2>
                <?php if ($sectionSubtitle): ?>
                    <p class="text-sm text-gray-500 mt-1"><?= $sectionSubtitle ?></p>
                <?php endif; ?>
            </div>
            <?php if ($viewAllLabel): ?>
                <a href="<?= $viewAllUrl ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium"><?= $viewAllLabel ?> →</a>
            <?php endif; ?>
        </div>

        <!-- Preview notice -->
        <div class="mb-4 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
            <strong>Preview:</strong> Real events will load from the <code><?= $collectionKey ?: '(collection_key)' ?></code> collection.
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <?php foreach ($sampleEvents as $event): ?>
                <article class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <img src="<?= $event['image'] ?>" alt="<?= esc($event['title']) ?>" class="w-full h-32 object-cover" />
                    <div class="p-3">
                        <span class="text-[10px] font-semibold text-blue-600 uppercase tracking-wide"><?= esc($event['category']) ?></span>
                        <h3 class="text-xs font-semibold text-gray-900 mt-0.5 leading-tight line-clamp-2"><?= esc($event['title']) ?></h3>
                        <p class="text-[10px] text-gray-400 mt-1"><?= esc($event['date']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
