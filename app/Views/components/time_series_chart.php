<?php

use App\Support\TimeSeries\TimeSeriesChartDTO;

/** @var TimeSeriesChartDTO $chart */
$titleId = $chart->id . '-title';
$descriptionId = $chart->id . '-description';
$svgTitleId = $chart->id . '-svg-title';
$svgDescriptionId = $chart->id . '-svg-description';
?>
<section class="mt-6 min-w-0 overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm p-5" data-time-series="<?= esc($chart->id) ?>" data-state="<?= esc($chart->state) ?>" aria-labelledby="<?= esc($titleId) ?>">
    <h2 id="<?= esc($titleId) ?>" class="text-lg font-semibold text-gray-900"><?= esc($chart->title) ?></h2>
    <p id="<?= esc($descriptionId) ?>" class="mt-1 text-sm text-gray-500"><?= esc($chart->description) ?></p>

    <?php if ($chart->state === TimeSeriesChartDTO::READY): ?>
        <?php
        $width = 720;
        $height = 280;
        $paddingLeft = 48;
        $paddingRight = 20;
        $paddingTop = 24;
        $paddingBottom = 44;
        $plotWidth = $width - $paddingLeft - $paddingRight;
        $plotHeight = $height - $paddingTop - $paddingBottom;
        $points = $chart->series[0]->points;
        $pointCount = count($points);
        $maximum = 0.0;

        foreach ($chart->series as $series) {
            foreach ($series->points as $point) {
                $maximum = max($maximum, $point->value);
            }
        }

        $scaleMaximum = $maximum > 0 ? $maximum * 1.1 : 1.0;
        $formatValue = static function (float $value): string {
            return fmod($value, 1.0) === 0.0
                ? number_format($value, 0, '.', ',')
                : number_format($value, 2, '.', ',');
        };
        $seriesCoordinates = [];
        $svgPoints = [];
        foreach ($chart->series as $seriesIndex => $series) {
            $seriesCoordinates[$seriesIndex] = [];
            $seriesPoints = [];
            foreach ($series->points as $index => $point) {
                $x = $pointCount === 1
                    ? $paddingLeft + ($plotWidth / 2)
                    : $paddingLeft + (($index / ($pointCount - 1)) * $plotWidth);
                $y = $paddingTop + $plotHeight - (($point->value / $scaleMaximum) * $plotHeight);
                $seriesCoordinates[$seriesIndex][] = ['x' => $x, 'y' => $y];
                $seriesPoints[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }
            $svgPoints[] = $seriesPoints;
        }
        ?>
        <figure class="mt-5" aria-describedby="<?= esc($descriptionId) ?>">
            <svg class="block h-auto w-full" viewBox="0 0 <?= esc((string) $width) ?> <?= esc((string) $height) ?>" role="img" aria-labelledby="<?= esc($svgTitleId) ?>" aria-describedby="<?= esc($svgDescriptionId) ?>">
                <title id="<?= esc($svgTitleId) ?>"><?= esc($chart->title) ?></title>
                <desc id="<?= esc($svgDescriptionId) ?>"><?= esc($chart->description) ?></desc>
                <?php foreach ([1.0, 0.75, 0.5, 0.25, 0.0] as $ratio): ?>
                    <?php $y = $paddingTop + ($plotHeight * (1 - $ratio)); ?>
                    <line x1="<?= esc((string) $paddingLeft) ?>" y1="<?= esc(number_format($y, 2, '.', '')) ?>" x2="<?= esc((string) ($width - $paddingRight)) ?>" y2="<?= esc(number_format($y, 2, '.', '')) ?>" stroke="currentColor" stroke-opacity="0.12" class="text-gray-200" aria-hidden="true"></line>
                    <text x="<?= esc((string) ($paddingLeft - 8)) ?>" y="<?= esc(number_format($y + 4, 2, '.', '')) ?>" fill="currentColor" class="text-gray-400" font-size="11" text-anchor="end" aria-hidden="true"><?= esc($formatValue($scaleMaximum * $ratio)) ?></text>
                <?php endforeach; ?>
                <?php if ($points !== []): ?>
                    <text x="<?= esc(number_format($pointCount === 1 ? $paddingLeft + ($plotWidth / 2) : $paddingLeft, 2, '.', '')) ?>" y="<?= esc((string) ($height - 12)) ?>" fill="currentColor" class="text-gray-400" font-size="11" text-anchor="<?= $pointCount === 1 ? 'middle' : 'start' ?>" aria-hidden="true"><?= esc($points[0]->label) ?></text>
                    <?php if ($pointCount > 1): ?>
                        <text x="<?= esc(number_format($width - $paddingRight, 2, '.', '')) ?>" y="<?= esc((string) ($height - 12)) ?>" fill="currentColor" class="text-gray-400" font-size="11" text-anchor="end" aria-hidden="true"><?= esc($points[$pointCount - 1]->label) ?></text>
                    <?php endif; ?>
                <?php endif; ?>
                <?php foreach ($svgPoints as $index => $seriesPoints): ?>
                    <?php if ($pointCount === 1 && isset($seriesCoordinates[$index][0])): ?>
                        <line x1="<?= esc(number_format($seriesCoordinates[$index][0]['x'], 2, '.', '')) ?>" y1="<?= esc(number_format($paddingTop + $plotHeight, 2, '.', '')) ?>" x2="<?= esc(number_format($seriesCoordinates[$index][0]['x'], 2, '.', '')) ?>" y2="<?= esc(number_format($seriesCoordinates[$index][0]['y'], 2, '.', '')) ?>" stroke="currentColor" stroke-width="2" stroke-dasharray="5 5" stroke-opacity="0.35" class="<?= $index === 0 ? 'text-brand-600' : 'text-violet-600' ?>" aria-hidden="true"></line>
                    <?php endif; ?>
                    <polyline class="<?= $index === 0 ? 'text-brand-600' : 'text-violet-600' ?>" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="<?= esc(implode(' ', $seriesPoints)) ?>"></polyline>
                    <?php foreach ($seriesCoordinates[$index] ?? [] as $coordinate): ?>
                        <circle cx="<?= esc(number_format($coordinate['x'], 2, '.', '')) ?>" cy="<?= esc(number_format($coordinate['y'], 2, '.', '')) ?>" r="<?= $pointCount === 1 ? '5' : '3.5' ?>" fill="currentColor" class="<?= $index === 0 ? 'text-brand-600' : 'text-violet-600' ?>" aria-hidden="true"></circle>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </svg>
            <figcaption class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-600">
                <?php foreach ($chart->series as $index => $series): ?>
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full <?= $index === 0 ? 'bg-brand-600' : 'bg-violet-600' ?>" aria-hidden="true"></span>
                        <?= esc($series->label) ?> (<?= esc($series->unit) ?>)
                    </span>
                <?php endforeach; ?>
            </figcaption>
        </figure>

        <div class="mt-5 <?= esc(table_wrapper_class()) ?>">
            <div class="<?= esc(table_scroll_class()) ?>">
                <table class="<?= esc(table_class()) ?>">
                    <caption class="sr-only"><?= esc($chart->table->caption) ?></caption>
                    <thead class="<?= esc(table_head_class()) ?>">
                        <tr>
                            <th scope="col" class="<?= esc(table_th_class()) ?>"><?= esc($chart->table->labelHeader) ?></th>
                            <?php foreach ($chart->table->valueHeaders as $headerIndex => $header): ?>
                                <th scope="col" class="<?= esc(table_th_class()) ?>"><?= esc($header) ?> (<?= esc($chart->series[$headerIndex]->unit) ?>)</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="<?= esc(table_body_class()) ?>">
                        <?php foreach ($points as $pointIndex => $point): ?>
                            <tr class="<?= esc(table_row_class()) ?>">
                                <th scope="row" class="<?= esc(table_td_class()) ?>"><?= esc($point->label) ?></th>
                                <?php foreach ($chart->series as $series): ?>
                                    <td class="<?= esc(table_td_class('primary')) ?>"><?= esc($formatValue($series->points[$pointIndex]->value)) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p class="mt-5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm <?= $chart->state === TimeSeriesChartDTO::ERROR ? 'text-red-700' : 'text-gray-600' ?>" <?= $chart->state === TimeSeriesChartDTO::ERROR ? 'role="alert"' : 'role="status"' ?>><?= esc($chart->message) ?></p>
    <?php endif; ?>
</section>
