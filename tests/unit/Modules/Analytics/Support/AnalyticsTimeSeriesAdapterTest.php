<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Analytics\Support;

use App\Modules\Analytics\Support\AnalyticsTimeSeriesAdapter;
use App\Support\TimeSeries\TimeSeriesChartDTO;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AnalyticsTimeSeriesAdapterTest extends CIUnitTestCase
{
    private AnalyticsTimeSeriesAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new AnalyticsTimeSeriesAdapter();
    }

    public function testMapsNestedRowsToTwoAlignedSeries(): void
    {
        $chart = $this->adapter->fromResponse([
            'ok' => true,
            'data' => [
                'data' => [
                    ['period' => '2026-01-01', 'views' => 0, 'unique_visitors' => 0],
                    ['period' => '2026-01-02', 'views' => '12', 'unique_visitors' => 7],
                ],
            ],
        ]);

        $this->assertSame(TimeSeriesChartDTO::READY, $chart->state);
        $this->assertCount(2, $chart->series);
        $this->assertSame(['2026-01-01', '2026-01-02'], array_map(
            static fn ($point): string => $point->label,
            $chart->series[0]->points,
        ));
        $this->assertSame(0.0, $chart->series[0]->points[0]->value);
        $this->assertSame(7.0, $chart->series[1]->points[1]->value);
    }

    public function testNullMetricRowsAreAbsentFromBothSeries(): void
    {
        $chart = $this->adapter->fromResponse([
            'data' => [
                ['period' => '2026-01-01', 'views' => 10, 'unique_visitors' => 4],
                ['period' => '2026-01-02', 'views' => null, 'unique_visitors' => 5],
                ['period' => '2026-01-03', 'views' => 20, 'unique_visitors' => 8],
            ],
        ]);

        $this->assertSame(TimeSeriesChartDTO::READY, $chart->state);
        $this->assertCount(2, $chart->series[0]->points);
        $this->assertSame('2026-01-03', $chart->series[1]->points[1]->label);
    }

    public function testEmptyAndFailedResponsesBecomeExplicitStates(): void
    {
        $empty = $this->adapter->fromResponse(['ok' => true, 'data' => []]);
        $error = $this->adapter->fromResponse(['ok' => false, 'data' => []]);

        $this->assertSame(TimeSeriesChartDTO::EMPTY, $empty->state);
        $this->assertSame(TimeSeriesChartDTO::ERROR, $error->state);
        $this->assertNotSame('', $error->message);
    }

    /**
     * @dataProvider invalidResponseProvider
     */
    public function testInvalidResponsesBecomeErrorState(array $response): void
    {
        $chart = $this->adapter->fromResponse($response);

        $this->assertSame(TimeSeriesChartDTO::ERROR, $chart->state);
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function invalidResponseProvider(): iterable
    {
        yield 'malformed payload' => [['ok' => true, 'data' => ['unexpected' => true]]];
        yield 'unordered labels' => [['data' => [
            ['period' => '2026-01-02', 'views' => 1, 'unique_visitors' => 1],
            ['period' => '2026-01-01', 'views' => 1, 'unique_visitors' => 1],
        ]]];
        yield 'non numeric value' => [['data' => [
            ['period' => '2026-01-01', 'views' => 'not-a-number', 'unique_visitors' => 1],
        ]]];
        yield 'negative value' => [['data' => [
            ['period' => '2026-01-01', 'views' => -1, 'unique_visitors' => 1],
        ]]];
    }
}
