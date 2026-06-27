<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class CollectionFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/collections');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections');

        $result->assertStatus(200);
    }

    public function testEditRendersSlugComponent(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    'id' => 10,
                    'collection_key' => 'news',
                    'translations' => [
                        [
                            'language_id' => 1,
                            'slug' => 'news',
                            'name' => 'News',
                            'description' => null,
                        ],
                    ],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        $collectionMock->method('checkSlug')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['available' => true],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $languageMock = $this->createMock(LanguageApiService::class);
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
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections/10/edit');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $namePos = strpos($body, 'name="translations[0][name]"');
        $slugPos = strpos($body, 'name="translations[0][slug]"');
        $this->assertNotFalse($namePos);
        $this->assertNotFalse($slugPos);
        $this->assertLessThan($slugPos, $namePos);
        $this->assertStringContainsString('data-slug-check-url="', $body);
        $this->assertStringContainsString('data-slug-current-id="10"', $body);
        $this->assertStringContainsString('name="current_id" value="10"', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
    }

    public function testCreateRendersBlockTemplateBuilder(): void
    {
        $catalogMock = $this->createMock(BlockCatalogServiceInterface::class);
        $catalogMock->method('all')
            ->willReturn([
                [
                    'id' => 1,
                    'block_key' => 'rich_text',
                    'name' => 'Texto Enriquecido',
                    'description' => 'Bloque de contenido',
                    'icon' => 'align-left',
                ],
            ]);
        Services::injectMock('blockCatalogService', $catalogMock);

        $languageMock = $this->createMock(LanguageApiService::class);
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
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections/create');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString('name="block_template"', $body);
        $this->assertStringContainsString('collectionBlockTemplateBuilder(', $body);
        $this->assertStringContainsString('rich_text', $body);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testUpdateAllowsKeepingTheSameCollectionKey(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('update')
            ->with('10', $this->callback(static function (array $payload): bool {
                return ($payload['collection_key'] ?? null) === 'news';
            }))
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/10', [
            csrf_token() => csrf_hash(),
            'current_id' => '10',
            'collection_key' => 'news',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'News',
                    'slug' => 'news',
                    'description' => '',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(CollectionApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/collections'));
    }
}
