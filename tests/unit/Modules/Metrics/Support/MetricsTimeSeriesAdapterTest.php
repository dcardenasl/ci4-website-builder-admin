<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Metrics\Support;

use App\Modules\Metrics\Support\MetricsTimeSeriesAdapter;
use App\Support\TimeSeries\TimeSeriesChartDTO;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MetricsTimeSeriesAdapterTest extends CIUnitTestCase
{
    private MetricsTimeSeriesAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new MetricsTimeSeriesAdapter();
    }

    public function testMapsParallelArraysWithoutReplacingNullsWithZeros(): void
    {
        $chart = $this->adapter->fromResponse([
            'ok' => true,
            'data' => [
                'data' => [
                    'dates' => ['2026-01-01', '2026-01-02', '2026-01-03'],
                    'requests' => [0, null, 12],
                ],
            ],
        ]);

        $this->assertSame(TimeSeriesChartDTO::READY, $chart->state);
        $this->assertCount(2, $chart->series[0]->points);
        $this->assertSame(0.0, $chart->series[0]->points[0]->value);
        $this->assertSame('2026-01-03', $chart->series[0]->points[1]->label);
    }

    public function testMapsPointRowsForCurrentServiceResponseShape(): void
    {
        $chart = $this->adapter->fromResponse([
            'data' => [
                ['period' => '2026-01-01', 'value' => 4],
                ['period' => '2026-01-02', 'requests' => '8'],
            ],
        ]);

        $this->assertSame(TimeSeriesChartDTO::READY, $chart->state);
        $this->assertSame(8.0, $chart->series[0]->points[1]->value);
    }

    public function testEmptyAndFailedResponsesBecomeExplicitStates(): void
    {
        $empty = $this->adapter->fromResponse(['ok' => true, 'data' => []]);
        $error = $this->adapter->fromResponse(['ok' => false, 'data' => []]);

        $this->assertSame(TimeSeriesChartDTO::EMPTY, $empty->state);
        $this->assertSame(TimeSeriesChartDTO::ERROR, $error->state);
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
        yield 'mismatched arrays' => [['data' => [
            'dates' => ['2026-01-01'],
            'requests' => [1, 2],
        ]]];
        yield 'unordered rows' => [['data' => [
            ['period' => '2026-01-02', 'value' => 1],
            ['period' => '2026-01-01', 'value' => 1],
        ]]];
        yield 'non numeric row' => [['data' => [
            ['period' => '2026-01-01', 'value' => 'invalid'],
        ]]];
    }
}
