<?php

declare(strict_types=1);

namespace Tests\Feature;

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
        $this->assertStringContainsString('name="block_template"', $body);
        $this->assertStringContainsString('collectionBlockTemplateBuilder(', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
    }

    public function testCreateRendersCollectionTypeAndTemplateEditor(): void
    {
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
        $this->assertStringContainsString('name="collection_type"', $body);
        $this->assertStringContainsString('name="collection_key"', $body);
        $this->assertStringContainsString('name="block_template"', $body);
        $this->assertStringContainsString('collectionBlockTemplateBuilder(', $body);
    }

    public function testStructureWizardShowsCollectionTypeAndHidesLegacyLanguageFields(): void
    {
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    ['id' => 1, 'code' => 'es', 'label' => 'Español', 'is_default' => true],
                    ['id' => 2, 'code' => 'en', 'label' => 'English', 'is_default' => false],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read', 'cms.entries.read', 'cms.pages.read', 'cms.pages.write', 'cms.menus.read', 'cms.menus.write']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/wizard/structure');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString('Resumen del preset', $body);
        $this->assertStringContainsString('Usar preset recomendado', $body);
        $this->assertStringContainsString('Crear sin preset', $body);
        $this->assertStringContainsString('Idiomas habilitados', $body);
        $this->assertStringContainsString('collection_translation_name_0', $body);
        $this->assertStringContainsString('collection_translation_slug_0', $body);
        $this->assertStringContainsString('Traducir todo', $body);
        $this->assertStringContainsString('Incluir', $body);
        $this->assertStringNotContainsString('name="collection_type"', $body);
        $this->assertStringContainsString('Idioma base', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
        $this->assertStringNotContainsString('default_language_id', $body);
    }

    public function testStructureWizardShowsDedicatedCollectionSuccessScreen(): void
    {
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    ['id' => 1, 'code' => 'es', 'label' => 'Español', 'is_default' => true],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read', 'cms.entries.read', 'cms.pages.read', 'cms.pages.write', 'cms.menus.read', 'cms.menus.write']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/wizard/structure');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString("x-show=\"screen === 'collection-success'\"", $body);
        $this->assertStringNotContainsString('lg:grid-cols-3', $body);
        $this->assertStringContainsString(lang('Wizard.wizard_structure_create_first_entry'), $body);
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
            'collection_type' => 'news',
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

    public function testStoreAcceptsDynamicCollectionType(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return ($payload['collection_type'] ?? null) === 'case-studies'
                    && ($payload['collection_key'] ?? null) === 'case-studies';
            }))
            ->willReturn([
                'ok' => true,
                'status' => 201,
                'data' => ['id' => 44],
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
        ])->post('/admin/cms/collections', [
            csrf_token() => csrf_hash(),
            'collection_type' => 'case-studies',
            'collection_key' => 'case-studies',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Case Studies',
                    'slug' => 'case-studies',
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
