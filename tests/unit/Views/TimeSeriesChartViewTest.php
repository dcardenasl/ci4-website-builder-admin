<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use App\Support\TimeSeries\TimeSeriesChartDTO;
use App\Support\TimeSeries\TimeSeriesPointDTO;
use App\Support\TimeSeries\TimeSeriesSeriesDTO;
use App\Support\TimeSeries\TimeSeriesTableMetadataDTO;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TimeSeriesChartViewTest extends CIUnitTestCase
{
    public function testReadyChartRendersSvgAndTheAccessibleTableFromTheSameSeries(): void
    {
        $series = [
            new TimeSeriesSeriesDTO('views', 'Views', 'views', [
                new TimeSeriesPointDTO('2026-01-01', 0.0),
                new TimeSeriesPointDTO('2026-01-02', 12.0),
            ]),
        ];
        $chart = TimeSeriesChartDTO::ready(
            'traffic',
            'Traffic Trend',
            'Daily traffic.',
            $series,
            new TimeSeriesTableMetadataDTO('Traffic data', 'Period', ['Views']),
        );

        $html = view('components/time_series_chart', ['chart' => $chart], ['saveData' => false]);

        $this->assertStringContainsString('data-state="ready"', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('<title id="traffic-svg-title">Traffic Trend</title>', $html);
        $this->assertStringContainsString('<desc id="traffic-svg-description">Daily traffic.</desc>', $html);
        $this->assertStringContainsString('text-anchor="end" aria-hidden="true">13.20</text>', $html);
        $this->assertStringContainsString('<caption class="sr-only">Traffic data</caption>', $html);
        $this->assertStringContainsString('Views (views)', $html);
        $this->assertStringContainsString('2026-01-02', $html);
        $this->assertStringContainsString('12', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function testSinglePointChartRendersAVisibleReferenceMarker(): void
    {
        $chart = TimeSeriesChartDTO::ready(
            'traffic',
            'Traffic Trend',
            'Daily traffic.',
            [new TimeSeriesSeriesDTO('views', 'Views', 'views', [new TimeSeriesPointDTO('2026-01-01', 3.0)])],
            new TimeSeriesTableMetadataDTO('Traffic data', 'Period', ['Views']),
        );

        $html = view('components/time_series_chart', ['chart' => $chart], ['saveData' => false]);

        $this->assertStringContainsString('stroke-dasharray="5 5"', $html);
        $this->assertStringContainsString('r="5"', $html);
        $this->assertStringContainsString('2026-01-01', $html);
    }

    public function testEmptyChartRendersVisibleStatusWithoutAnEmptySvg(): void
    {
        $chart = TimeSeriesChartDTO::empty(
            'traffic',
            'Traffic Trend',
            'Daily traffic.',
            new TimeSeriesTableMetadataDTO('Traffic data', 'Period', ['Views']),
            'No traffic data.',
        );

        $html = view('components/time_series_chart', ['chart' => $chart], ['saveData' => false]);

        $this->assertStringContainsString('data-state="empty"', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('No traffic data.', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }
}
