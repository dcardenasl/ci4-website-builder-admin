<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$sectionTitle  = esc($data['section_title'] ?? 'Últimas Noticias');
$viewAllLabel  = esc($data['view_all_label'] ?? 'Ver todas las noticias');
$viewAllUrl    = esc($data['view_all_url'] ?? '#');
$collectionKey = esc($config['collection_key'] ?? 'noticias');
$cssClass      = esc($config['css_class'] ?? '');

$sampleNews = [
    [
        'title'   => 'Exciting News Article Title',
        'excerpt' => 'A brief excerpt that summarizes the main point of this news article.',
        'date'    => '10 Jun 2025',
        'image'   => 'https://placehold.co/600x400/1a1a2e/ffffff?text=Article+1',
    ],
    [
        'title'   => 'Another Great Story',
        'excerpt' => 'More details about what is happening with your content and updates.',
        'date'    => '2 Jun 2025',
        'image'   => 'https://placehold.co/600x400/16213e/ffffff?text=Article+2',
    ],
    [
        'title'   => 'Featured Update',
        'excerpt' => 'Important information that your audience should know about right now.',
        'date'    => '25 May 2025',
        'image'   => 'https://placehold.co/600x400/0f3460/ffffff?text=Article+3',
    ],
];
?>
<section class="py-10 bg-gray-50 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?= $sectionTitle ?></h2>
            <?php if ($viewAllLabel): ?>
                <a href="<?= $viewAllUrl ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium"><?= $viewAllLabel ?> →</a>
            <?php endif; ?>
        </div>

        <div class="mb-4 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
            <strong>Preview:</strong> Real news items will load from the <code><?= $collectionKey ?: '(collection_key)' ?></code> collection.
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <?php foreach ($sampleNews as $news): ?>
                <article class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <img src="<?= $news['image'] ?>" alt="<?= esc($news['title']) ?>" class="w-full h-40 object-cover" />
                    <div class="p-4">
                        <p class="text-[10px] text-gray-400 mb-1"><?= esc($news['date']) ?></p>
                        <h3 class="text-sm font-semibold text-gray-900 leading-tight mb-2 line-clamp-2"><?= esc($news['title']) ?></h3>
                        <p class="text-xs text-gray-500 line-clamp-3"><?= esc($news['excerpt']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
