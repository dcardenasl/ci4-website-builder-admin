<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\BlockInstanceApiService;
use App\Modules\Cms\Services\BlockTypeApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class BlockInstanceFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testIndexRequiresAuth(): void
    {
        $result = $this->get('/admin/cms/pages/1/blocks');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'title' => 'Test Page'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->method('list')
            ->with('1', 'page')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $typeMock = $this->createMock(BlockTypeApiService::class);
        $typeMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockTypeApiService', $typeMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.read']],
        ])->get('/admin/cms/pages/1/blocks');

        $result->assertStatus(200);
    }

    public function testEntryIndexRendersForAdmin(): void
    {
        $entryMock = $this->createMock(EntryApiService::class);
        $entryMock->method('get')
            ->with('4')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 4, 'title' => 'Test Entry'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('entryApiService', $entryMock);

        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->method('list')
            ->with('4', 'entry')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $typeMock = $this->createMock(BlockTypeApiService::class);
        $typeMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockTypeApiService', $typeMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/entries/4/blocks');

        $result->assertStatus(200);
    }

    public function testCreateRendersForAdmin(): void
    {
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'title' => 'Test Page'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $typeMock = $this->createMock(BlockTypeApiService::class);
        $typeMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockTypeApiService', $typeMock);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->get('/admin/cms/pages/1/blocks/create');

        $result->assertStatus(200);
    }

    public function testEntryCreateRendersForAdmin(): void
    {
        $entryMock = $this->createMock(EntryApiService::class);
        $entryMock->method('get')
            ->with('4')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 4, 'title' => 'Test Entry'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('entryApiService', $entryMock);

        $typeMock = $this->createMock(BlockTypeApiService::class);
        $typeMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockTypeApiService', $typeMock);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.entries.write', 'cms.entries.read']],
        ])->get('/admin/cms/entries/4/blocks/create');

        $result->assertStatus(200);
    }

    public function testStoreSuccessfullyRedirects(): void
    {
        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->expects($this->once())
            ->method('create')
            ->with('1', 'page', $this->callback(function ($payload) {
                return $payload['block_id'] === 5 && $payload['owner_id'] === 1 && $payload['owner_type'] === 'page';
            }))
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 10],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages/1/blocks/store', [
            csrf_token() => csrf_hash(),
            'block_id' => '5',
            'sort_order' => '1',
            'is_active' => '1',
            'block_config' => '{"foo":"bar"}',
            'translations' => [
                [
                    'language_id' => '1',
                    'block_data' => ['content' => 'hello'],
                    'is_published' => '1',
                ]
            ]
        ]);

        $result->assertRedirectTo(site_url('admin/cms/pages/1/blocks'));
    }

    public function testUpdateValidationFailureKeepsFieldErrorsInSession(): void
    {
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'title' => 'Test Page'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->expects($this->once())
            ->method('get')
            ->with('1', 'page', '10')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    'id' => 10,
                    'block_id' => 5,
                    'parent_instance_id' => null,
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        $blockMock->expects($this->once())
            ->method('update')
            ->with('1', 'page', '10', $this->callback(static fn (array $payload): bool => ($payload['sort_order'] ?? null) === 2))
            ->willReturn([
                'ok' => false,
                'status' => 422,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ['validationFailed'],
                'fieldErrors' => [
                    'sort_order' => 'Sort order is required',
                    'translations.0.block_data.title' => 'Title is required',
                ],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages/1/blocks/10', [
            csrf_token() => csrf_hash(),
            'block_id' => '5',
            'sort_order' => '2',
            'is_active' => '1',
            'translations' => [
                [
                    'language_id' => '1',
                    'block_data' => ['title' => ''],
                    'is_published' => '1',
                ],
            ],
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('fieldErrors');
        $result->assertSessionMissing('error');
    }

    public function testDeleteRedirects(): void
    {
        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->expects($this->once())
            ->method('delete')
            ->with('1', 'page', '10')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages/1/blocks/10/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/pages/1/blocks'));
    }

    public function testEditRendersWithCollectionOptions(): void
    {
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'title' => 'Test Page'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->method('get')
            ->with('1', 'page', '10')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    'id' => 10,
                    'block_id' => 5,
                    'parent_instance_id' => null,
                    'block_config' => ['collection_key' => 'noticias_custom'],
                    'translations' => [],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $typeMock = $this->createMock(BlockTypeApiService::class);
        $typeMock->method('get')
            ->with(5)
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    'id' => 5,
                    'block_key' => 'news_grid',
                    'schema_definition' => [
                        'config_fields' => [
                            'collection_key' => ['type' => 'string', 'label' => 'Clave de Colección (CMS)', 'required' => true, 'default' => 'noticias'],
                        ],
                    ],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockTypeApiService', $typeMock);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    ['id' => 1, 'code' => 'es', 'is_default' => 1, 'is_active' => true],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $langMock);

        $collectionMock = $this->createMock(\App\Modules\Cms\Services\CollectionApiServiceInterface::class);
        $collectionMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    ['collection_key' => 'noticias_active'],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->get('/admin/cms/pages/1/blocks/10/edit');

        $result->assertStatus(200);
        $result->assertSee('noticias_active');
        $result->assertSee('noticias_custom');
    }

    public function testIndexRendersCollectionActionButtons(): void
    {
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'title' => 'Test Page'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $blockMock = $this->createMock(BlockInstanceApiService::class);
        $blockMock->method('list')
            ->with('1', 'page')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    [
                        'id' => 10,
                        'block_id' => 5,
                        'parent_instance_id' => null,
                        'block_config' => ['collection_key' => 'noticias_active'],
                        'is_active' => true,
                        'translations' => [],
                    ]
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockInstanceApiService', $blockMock);

        $typeMock = $this->createMock(BlockTypeApiService::class);
        $typeMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    [
                        'id' => 5,
                        'block_key' => 'news_grid',
                        'name' => 'Grilla de Noticias',
                        'icon' => 'newspaper',
                        'schema_definition' => [
                            'config_fields' => [
                                'collection_key' => ['type' => 'string', 'label' => 'Clave de Colección (CMS)', 'required' => true, 'default' => 'noticias'],
                            ],
                        ],
                    ]
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('blockTypeApiService', $typeMock);

        $collectionMock = $this->createMock(\App\Modules\Cms\Services\CollectionApiServiceInterface::class);
        $collectionMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    ['id' => 42, 'collection_key' => 'noticias_active'],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.read']],
        ])->get('/admin/cms/pages/1/blocks');

        $result->assertStatus(200);
        $result->assertSee('/admin/cms/entries?collection_id=42');
        $result->assertSee('/admin/cms/entries/create?collection_id=42');
    }
}
