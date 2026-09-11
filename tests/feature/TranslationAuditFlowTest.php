<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\TranslationAuditApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class TranslationAuditFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testReportForwardsAllContextualFilters(): void
    {
        $audit = $this->createMock(TranslationAuditApiService::class);
        $audit->expects($this->once())->method('getReport')->with([
            'language_id' => 3,
            'resource' => 'page',
            'status' => 'missing',
            'search' => 'home',
            'page' => 1,
            'limit' => 25,
        ])->willReturn([
            'ok' => true, 'status' => 200, 'data' => [
                'items' => [['resource' => 'page']],
                'meta' => ['page' => 1, 'per_page' => 25, 'total_items' => 1, 'last_page' => 1],
            ],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
        ]);
        Services::injectMock('translationAuditApiService', $audit);

        $response = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.languages.read']],
        ])->get('/admin/cms/translations/audit/data?language_id=3&resource=page&status=missing&search=home');

        $response->assertStatus(200);
        $this->assertStringContainsString('"resource": "page"', (string) $response->getBody());
        $this->assertStringContainsString('"recordsTotal": 1', (string) $response->getBody());
    }

    public function testReportForwardsPageAndLimitAndReturnsBoundedMeta(): void
    {
        $audit = $this->createMock(TranslationAuditApiService::class);
        $audit->expects($this->once())->method('getReport')->with([
            'page' => 2,
            'limit' => 10,
        ])->willReturn([
            'ok' => true,
            'status' => 200,
            'data' => [
                'items' => [['resource' => 'page', 'resource_id' => 11]],
                'meta' => ['page' => 2, 'per_page' => 10, 'total_items' => 21, 'last_page' => 3],
            ],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
        ]);
        Services::injectMock('translationAuditApiService', $audit);

        $response = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.languages.read']],
        ])->get('/admin/cms/translations/audit/data?page=2&limit=10');

        $response->assertStatus(200);
        $this->assertStringContainsString('"recordsTotal": 21', (string) $response->getBody());
        $this->assertStringContainsString('"page": 2', (string) $response->getBody());
    }

    public function testReportRequiresLanguageReadPermission(): void
    {
        $response = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => []],
        ])->get('/admin/cms/translations/audit/data');

        $response->assertRedirect();
    }
}
