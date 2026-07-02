<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class PageFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/pages');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/cms/pages');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $mock = $this->createMock(PageApiService::class);
        $mock->method('pages')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.read']],
        ])->get('/admin/cms/pages');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testCreateRendersPresetDrivenPageTypes(): void
    {
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('pages')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('defaultId')
            ->willReturn(1);
        $languageMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    ['id' => 1, 'code' => 'es', 'is_default' => true],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->get('/admin/cms/pages/create');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString('name="page_type"', $body);
        $this->assertStringContainsString('Nosotros', $body);
        $this->assertStringContainsString('Historia', $body);
        $this->assertStringContainsString('Eventos', $body);
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(PageApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('pageApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/pages'));
    }
}
