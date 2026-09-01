<?php

use App\Support\TimeSeries\TimeSeriesChartDTO;

/** @var TimeSeriesChartDTO $chart */
$titleId = $chart->id . '-title';
$descriptionId = $chart->id . '-description';
?>
<section class="mt-6 bg-white border border-gray-200 rounded-xl shadow-sm p-5" data-time-series="<?= esc($chart->id) ?>" data-state="<?= esc($chart->state) ?>" aria-labelledby="<?= esc($titleId) ?>">
    <h2 id="<?= esc($titleId) ?>" class="text-lg font-semibold text-gray-900"><?= esc($chart->title) ?></h2>
    <p id="<?= esc($descriptionId) ?>" class="mt-1 text-sm text-gray-500"><?= esc($chart->description) ?></p>

    <?php if ($chart->state === TimeSeriesChartDTO::READY): ?>
        <?php
        $width = 720;
        $height = 280;
        $paddingLeft = 12;
        $paddingRight = 12;
        $paddingTop = 20;
        $paddingBottom = 20;
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

        $scaleMaximum = $maximum > 0 ? $maximum : 1.0;
        $formatValue = static function (float $value): string {
            return number_format($value, 0, '.', ',');
        };
        $svgPoints = [];
        foreach ($chart->series as $series) {
            $seriesPoints = [];
            foreach ($series->points as $index => $point) {
                $x = $pointCount === 1
                    ? $paddingLeft + ($plotWidth / 2)
                    : $paddingLeft + (($index / ($pointCount - 1)) * $plotWidth);
                $y = $paddingTop + $plotHeight - (($point->value / $scaleMaximum) * $plotHeight);
                $seriesPoints[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }
            $svgPoints[] = $seriesPoints;
        }
        ?>
        <figure class="mt-5" aria-describedby="<?= esc($descriptionId) ?>">
            <svg class="block h-auto w-full" viewBox="0 0 <?= esc((string) $width) ?> <?= esc((string) $height) ?>" role="img" aria-labelledby="<?= esc($titleId) ?>" aria-describedby="<?= esc($descriptionId) ?>">
                <line x1="<?= esc((string) $paddingLeft) ?>" y1="<?= esc((string) ($paddingTop + $plotHeight)) ?>" x2="<?= esc((string) ($width - $paddingRight)) ?>" y2="<?= esc((string) ($paddingTop + $plotHeight)) ?>" stroke="currentColor" stroke-opacity="0.15" aria-hidden="true"></line>
                <line x1="<?= esc((string) $paddingLeft) ?>" y1="<?= esc((string) $paddingTop) ?>" x2="<?= esc((string) $paddingLeft) ?>" y2="<?= esc((string) ($paddingTop + $plotHeight)) ?>" stroke="currentColor" stroke-opacity="0.15" aria-hidden="true"></line>
                <?php foreach ($svgPoints as $index => $seriesPoints): ?>
                    <polyline class="<?= $index === 0 ? 'text-brand-600' : 'text-violet-600' ?>" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="<?= esc(implode(' ', $seriesPoints)) ?>"></polyline>
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
                            <?php foreach ($chart->table->valueHeaders as $header): ?>
                                <th scope="col" class="<?= esc(table_th_class()) ?>"><?= esc($header) ?></th>
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
