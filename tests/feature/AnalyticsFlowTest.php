<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Analytics\Services\AnalyticsApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class AnalyticsFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAnalyticsPageRendersTheTypedTrafficChartAndAccessibleTable(): void
    {
        $analyticsService = $this->createMock(AnalyticsApiService::class);
        $analyticsService->expects($this->once())
            ->method('overview')
            ->with(['period' => '7d'])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['total_views' => 321, 'unique_visitors' => 123],
            ]);
        $analyticsService->expects($this->once())
            ->method('pages')
            ->with(['period' => '7d', 'limit' => 10])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
            ]);
        $analyticsService->expects($this->once())
            ->method('referrers')
            ->with(['period' => '7d', 'limit' => 10])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
            ]);
        $analyticsService->expects($this->once())
            ->method('devices')
            ->with(['period' => '7d'])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
            ]);
        $analyticsService->expects($this->once())
            ->method('timeseries')
            ->with(['period' => '7d'])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    ['period' => '2026-01-01', 'views' => 0, 'unique_visitors' => 0],
                    ['period' => '2026-01-02', 'views' => 12, 'unique_visitors' => 7],
                ],
            ]);

        Services::injectMock('analyticsApiService', $analyticsService);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.analytics.read']],
        ])->get('/admin/analytics');

        $result->assertStatus(200);
        $this->assertStringContainsString('321', $result->getBody());
        $this->assertStringContainsString('data-state="ready"', $result->getBody());
        $this->assertStringContainsString('<svg', $result->getBody());
        $this->assertStringContainsString('Datos de tendencia de tr&aacute;fico', $result->getBody());
        $this->assertStringContainsString('2026-01-02', $result->getBody());
    }
}
