<?php

declare(strict_types=1);

namespace Tests\Unit\Support\TimeSeries;

use App\Support\TimeSeries\TimeSeriesChartDTO;
use App\Support\TimeSeries\TimeSeriesPointDTO;
use App\Support\TimeSeries\TimeSeriesSeriesDTO;
use App\Support\TimeSeries\TimeSeriesTableMetadataDTO;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class TimeSeriesChartDTOTest extends CIUnitTestCase
{
    public function testPointRejectsBlankAndUnsafeValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TimeSeriesPointDTO(' ', 1.0);
    }

    public function testPointRejectsNegativeValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TimeSeriesPointDTO('2026-01-01', -1.0);
    }

    public function testSeriesRejectsMoreThanTheMaximumNumberOfPoints(): void
    {
        $points = [];
        for ($index = 0; $index <= TimeSeriesSeriesDTO::MAX_POINTS; $index++) {
            $points[] = new TimeSeriesPointDTO((string) $index, 1.0);
        }

        $this->expectException(InvalidArgumentException::class);
        new TimeSeriesSeriesDTO('requests', 'Requests', 'requests', $points);
    }

    public function testReadyChartRequiresAlignedSeriesAndTableHeaders(): void
    {
        $table = new TimeSeriesTableMetadataDTO('Caption', 'Period', ['Views', 'Visitors']);
        $first = new TimeSeriesSeriesDTO('views', 'Views', 'views', [new TimeSeriesPointDTO('2026-01-01', 1.0)]);
        $second = new TimeSeriesSeriesDTO('visitors', 'Visitors', 'visitors', [new TimeSeriesPointDTO('2026-01-02', 1.0)]);

        $this->expectException(InvalidArgumentException::class);
        TimeSeriesChartDTO::ready('traffic', 'Traffic', 'Description', [$first, $second], $table);
    }

    public function testReadyChartExposesTypedAlignedData(): void
    {
        $table = new TimeSeriesTableMetadataDTO('Caption', 'Period', ['Views']);
        $series = new TimeSeriesSeriesDTO(
            'views',
            'Views',
            'views',
            [new TimeSeriesPointDTO('2026-01-01', 0.0)],
        );

        $chart = TimeSeriesChartDTO::ready('traffic', 'Traffic', 'Description', [$series], $table);

        $this->assertSame(TimeSeriesChartDTO::READY, $chart->state);
        $this->assertSame(0.0, $chart->series[0]->points[0]->value);
    }

    public function testEmptyAndErrorChartsRequireNoSeriesAndErrorMessage(): void
    {
        $table = new TimeSeriesTableMetadataDTO('Caption', 'Period', ['Requests']);
        $empty = TimeSeriesChartDTO::empty('traffic', 'Traffic', 'Description', $table, 'No data');

        $this->assertSame(TimeSeriesChartDTO::EMPTY, $empty->state);
        $this->assertSame('No data', $empty->message);

        $this->expectException(InvalidArgumentException::class);
        TimeSeriesChartDTO::error('traffic', 'Traffic', 'Description', $table, ' ');
    }
}
