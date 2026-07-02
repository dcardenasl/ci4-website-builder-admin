<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\DomainApiClientInterface;
use App\Modules\Cms\Controllers\WizardController;
use App\Modules\Cms\Controllers\StructureWizardController;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class WizardFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/wizard');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/wizard');

        $result->assertStatus(200);
        $result->assertSee('¿Qué quieres hacer hoy?');
        $result->assertSee('Volver al panel');
        $result->assertSee('Reintentar');
    }

    public function testStructureWizardIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user' => ['permissions' => [
                'cms.entries.read',
                'cms.collections.read',
                'cms.collections.write',
                'cms.pages.read',
                'cms.pages.write',
                'cms.menus.read',
                'cms.menus.write',
            ]],
        ])->get('/admin/cms/wizard/structure');

        $result->assertStatus(200);
        $result->assertSee('¿Qué quieres construir hoy?');
        $result->assertSee('Crear colección');
        $result->assertSee('Crear menú');
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Resumen del preset', $body);
        $this->assertStringContainsString('collectionErrors.slug_base', $body);
        $this->assertStringContainsString('data-slug-invalid-message', $body);
        $this->assertStringNotContainsString('name="collection_type"', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
        $this->assertStringNotContainsString('Idioma base', $body);
    }

    public function testStructureWizardConfigPassesThroughDefaultLanguageIdFromLanguageService(): void
    {
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    ['id' => 1, 'code' => 'es', 'is_default' => true],
                    ['id' => 2, 'code' => 'en', 'is_default' => false],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $languageMock->expects($this->once())
            ->method('defaultId')
            ->willReturn(1);
        Services::injectMock('languageApiService', $languageMock);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->config();

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('languages', $body['data']);
        $this->assertArrayHasKey('collection_presets', $body['data']);
        $this->assertArrayHasKey('default_language_id', $body['data']);
        $this->assertSame(1, $body['data']['default_language_id']);
        $this->assertNotEmpty($body['data']['collection_presets']['blog']['block_template']['blocks']);
    }

    public function testConfigUnwrapsDomainPayload(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $configResponse = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'status' => 'success',
                'data' => [
                    'default_language_id' => 1,
                    'languages' => [
                        ['id' => 1, 'code' => 'es', 'is_default' => true],
                    ],
                    'collections' => [
                        ['id' => 1, 'name' => 'Noticias'],
                    ],
                    'pages' => [
                        ['id' => 2, 'title' => 'Inicio'],
                    ],
                    'menus' => [
                        ['id' => 3, 'name' => 'Main'],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];

        $blockTypesResponse = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'items' => [
                    [
                        'id' => 11,
                        'block_key' => 'rich_text',
                        'name' => 'Rich Text',
                        'description' => 'Editor de texto enriquecido',
                        'icon' => 'align-left',
                        'schema_definition' => ['fields' => []],
                        'supports_pages' => true,
                        'supports_entries' => true,
                        'is_container' => false,
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];

        $mock->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($configResponse, $blockTypesResponse);

        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->config();

        $this->assertSame(200, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('collections', $body);
        $this->assertArrayHasKey('pages', $body);
        $this->assertArrayHasKey('menus', $body);
        $this->assertArrayHasKey('default_language_id', $body);
        $this->assertArrayHasKey('collection_types', $body);
        $this->assertArrayHasKey('page_types', $body);
        $this->assertArrayHasKey('languages', $body);
        $this->assertSame(1, $body['default_language_id']);
        $this->assertTrue($body['languages'][0]['is_default']);
        $this->assertCount(1, $body['collections']);
        $this->assertCount(1, $body['pages']);
        $this->assertCount(1, $body['menus']);
        $this->assertNotEmpty($body['collection_types']);
        $this->assertNotEmpty($body['page_types']);
        $this->assertSame(11, $body['block_types']['rich_text']['id']);
        $this->assertTrue($body['block_types']['rich_text']['supports_entries']);
    }

    public function testEntryBlocksUnwrapsDomainPayload(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/entries/7/blocks', ['include_translations' => 1, 'limit' => 100])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'items' => [
                        ['id' => 99, 'block_config' => ['block_key' => 'rich_text']],
                    ],
                    'raw' => '',
                    'headers' => [],
                    'messages' => [],
                    'fieldErrors' => [],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->entryBlocks(7);

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('items', $body);
        $this->assertSame(99, $body['items'][0]['id']);
    }

    // ── publish() validation ──────────────────────────────────────────────────

    public function testPublishRejectsEmptyPayload(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(400, $result->getStatusCode());
    }

    public function testPublishRejectsMissingCollectionId(): void
    {
        $this->injectJsonBody(['title' => 'Test', 'status' => 'draft']);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('collection_id', $body['errors']);
    }

    public function testPublishRejectsMissingTitle(): void
    {
        $this->injectJsonBody(['collection_id' => 1, 'title' => '  ', 'status' => 'draft']);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('title', $body['errors']);
    }

    public function testPublishRejectsInvalidStatus(): void
    {
        $this->injectJsonBody(['collection_id' => 1, 'title' => 'Test', 'status' => 'scheduled']);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('status', $body['errors']);
    }

    public function testPublishForwardsValidPayloadToDomain(): void
    {
        $payload = ['collection_id' => 1, 'title' => 'My Entry', 'status' => 'published'];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries', $payload)
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 42, 'title' => 'My Entry'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(201, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertSame(42, $body['id']);
    }

    public function testStructureWizardCreateCollectionSurfacesApiValidationDetail(): void
    {
        session()->set('user', ['permissions' => ['cms.collections.write']]);

        $collectionMock = $this->createMock(\App\Modules\Cms\Services\CollectionApiServiceInterface::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->willReturn([
                'ok' => false,
                'status' => 422,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ['Slug already exists'],
                'fieldErrors' => ['collection_key' => 'Slug already exists'],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $this->injectJsonBody([
            'collection_type' => 'blog',
            'collection_key' => 'blog',
            'sort_order' => 0,
            'translations' => [
                ['language_id' => 1, 'slug' => 'blog', 'name' => 'Blog'],
            ],
        ]);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createCollection();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('Slug already exists', $body['message']);
        $this->assertSame(['collection_key' => 'Slug already exists'], $body['fieldErrors']);
    }

    // ── uploadImage() validation ──────────────────────────────────────────────

    public function testValidateImageFileRejectsNonImageMime(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));

        $fakeFile = new class () {
            public function getMimeType(): string
            {
                return 'application/pdf';
            }
            public function getSize(): int
            {
                return 1024;
            }
        };

        $ref = new \ReflectionMethod($controller, 'validateImageFile');
        $ref->setAccessible(true);
        $error = $ref->invoke($controller, $fakeFile);

        $this->assertIsString($error);
        $this->assertStringContainsString('application/pdf', $error);
    }

    public function testValidateImageFileRejectsOversizedFile(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));

        $fakeFile = new class () {
            public function getMimeType(): string
            {
                return 'image/jpeg';
            }
            public function getSize(): int
            {
                return 20 * 1024 * 1024;
            } // 20 MB
        };

        $ref = new \ReflectionMethod($controller, 'validateImageFile');
        $ref->setAccessible(true);
        $error = $ref->invoke($controller, $fakeFile);

        $this->assertIsString($error);
        $this->assertStringContainsString('MB', $error);
    }

    public function testValidateImageFileAcceptsValidImage(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));

        $fakeFile = new class () {
            public function getMimeType(): string
            {
                return 'image/jpeg';
            }
            public function getSize(): int
            {
                return 500 * 1024;
            } // 500 KB
        };

        $ref = new \ReflectionMethod($controller, 'validateImageFile');
        $ref->setAccessible(true);
        $error = $ref->invoke($controller, $fakeFile);

        $this->assertNull($error);
    }

    // ── createBlock() / createEntryBlock() validation ────────────────────────

    public function testCreateBlockRejectsMissingBlockTypeKey(): void
    {
        $this->injectJsonBody(['block_data' => ['text' => 'hello']]);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createBlock(1);

        $this->assertSame(400, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertStringContainsString('block_type_key', $body['message']);
    }

    public function testCreateBlockForwardsValidPayloadToDomain(): void
    {
        $payload = ['block_type_key' => 'rich_text', 'block_data' => ['text' => 'hello']];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/pages/5/blocks', $payload)
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 10],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createBlock(5);

        $this->assertSame(201, $result->getStatusCode());
    }

    public function testCreateEntryBlockRejectsMissingBlockTypeKey(): void
    {
        $this->injectJsonBody(['block_data' => []]);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createEntryBlock(3);

        $this->assertSame(400, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertStringContainsString('block_type_key', $body['message']);
    }

    public function testCreateEntryBlockForwardsValidPayloadToDomain(): void
    {
        $payload = ['block_type_key' => 'hero', 'block_data' => ['title' => 'Hi']];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries/3/blocks', $payload)
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 20],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createEntryBlock(3);

        $this->assertSame(201, $result->getStatusCode());
    }

    // ── menu item mutations ───────────────────────────────────────────────────

    public function testAddMenuItemForwardsPayloadToDomain(): void
    {
        $payload = ['label' => 'Inicio', 'url' => '/'];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/menu-items', array_merge($payload, ['menu_id' => 2]))
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 7],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->addMenuItem(2);

        $this->assertSame(201, $result->getStatusCode());
    }

    public function testUpdateMenuItemForwardsPayloadToDomain(): void
    {
        $payload = ['label' => 'Contacto', 'url' => '/contacto'];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('put')
            ->with('/cms/menu-items/9', $payload)
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['id' => 9],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->updateMenuItem(9);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testDeleteMenuItemForwardsToDomain(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/menu-items/9')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->deleteMenuItem(9);

        $this->assertSame(200, $result->getStatusCode());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Replaces the CI4 request body with a JSON payload so controller methods
     * that call $this->request->getJSON(true) receive the expected data.
     *
     * @param array<string, mixed> $data
     */
    private function injectJsonBody(array $data): void
    {
        $request = Services::request();
        $request->setBody((string) json_encode($data));
        $request->setHeader('Content-Type', 'application/json');
        Services::injectMock('request', $request);
    }
}
