<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Services\MenuApiServiceInterface;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class MenuItemFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testCreateRendersEntryAndCollectionSelectors(): void
    {
        $menuMock = $this->createMock(MenuApiServiceInterface::class);
        $menuMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'menu_key' => 'main'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        $menuMock->method('listItems')
            ->with($this->callback(static fn (array $filters): bool => ($filters['menu_id'] ?? null) === '1'))
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('menuApiService', $menuMock);

        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('pages')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['items' => [['id' => 2, 'translations' => [['title' => 'Home']]]]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $entryMock = $this->createMock(EntryApiService::class);
        $entryMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['items' => [['id' => 9, 'title' => 'Noticias destacadas']]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        $entryMock->method('collections')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['items' => [['id' => 7, 'collection_key' => 'noticias']]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('entryApiService', $entryMock);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['items' => [['id' => 1, 'name' => 'ES']]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->get('/admin/cms/menus/1/items/create');

        $result->assertStatus(200);
        $this->assertStringContainsString('name="entry_id"', (string) $result->getBody());
        $this->assertStringContainsString('name="collection_id"', (string) $result->getBody());
        $this->assertStringContainsString('value="entry"', (string) $result->getBody());
        $this->assertStringContainsString('value="collection_listing"', (string) $result->getBody());
    }

    public function testUpdateAcceptsCollectionListingTarget(): void
    {
        $menuMock = $this->createMock(MenuApiServiceInterface::class);
        $menuMock->expects($this->once())
            ->method('updateItem')
            ->with('2', $this->callback(static function (array $payload): bool {
                return $payload['menu_id'] === 1
                    && $payload['link_type'] === 'collection_listing'
                    && $payload['page_id'] === null
                    && $payload['entry_id'] === null
                    && $payload['collection_id'] === 7
                    && $payload['sort_order'] === 4
                    && $payload['translations'][0]['label'] === 'Noticias';
            }))
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 2],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('menuApiService', $menuMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->post('/admin/cms/menus/1/items/2', [
            csrf_token() => csrf_hash(),
            'menu_id' => '1',
            'parent_id' => '',
            'link_type' => 'collection_listing',
            'collection_id' => '7',
            'link_target' => '_self',
            'icon' => '',
            'css_class' => '',
            'sort_order' => '4',
            'is_active' => '1',
            'translations' => [
                1 => [
                    'label' => 'Noticias',
                ],
            ],
        ]);

        $result->assertRedirectTo(site_url('admin/cms/menus/1'));
    }

    public function testReorderItemsRendersComponent(): void
    {
        $menuMock = $this->createMock(MenuApiServiceInterface::class);
        $menuMock->method('get')
            ->with('1')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['id' => 1, 'menu_key' => 'main'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        $menuMock->method('listItems')
            ->with($this->callback(static fn (array $filters): bool => ($filters['menu_id'] ?? null) === '1'))
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    ['id' => 1, 'menu_id' => 1, 'sort_order' => 1, 'label' => 'Item 1'],
                    ['id' => 2, 'menu_id' => 1, 'sort_order' => 0, 'label' => 'Item 2'],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('menuApiService', $menuMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->get('/admin/cms/menus/1/items/reorder');

        $result->assertStatus(200);
        $this->assertStringContainsString('Reordenar', (string) $result->getBody());
    }

    public function testSaveItemsOrderUpdatesAndReturnsOk(): void
    {
        $menuMock = $this->createMock(MenuApiServiceInterface::class);
        $menuMock->method('listItems')
            ->with($this->callback(static fn (array $filters): bool => ($filters['menu_id'] ?? null) === '1'))
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [
                    ['id' => 1, 'menu_id' => 1, 'sort_order' => 1, 'translations' => []],
                    ['id' => 2, 'menu_id' => 1, 'sort_order' => 0, 'translations' => []],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        $menuMock->expects($this->exactly(2))
            ->method('updateItem')
            ->willReturnCallback(static function (string $id, array $payload): array {
                if ($id === '2') {
                    self::assertSame(0, $payload['sort_order']);
                } elseif ($id === '1') {
                    self::assertSame(1, $payload['sort_order']);
                }
                return ['ok' => true];
            });
        Services::injectMock('menuApiService', $menuMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            csrf_header()      => csrf_hash(),
            'Content-Type'     => 'application/json',
        ])->withBody(json_encode([
            'items' => [
                ['id' => 2, 'sort_order' => 0],
                ['id' => 1, 'sort_order' => 1],
            ],
        ]))->post('/admin/cms/menus/1/items/reorder');

        $result->assertStatus(200);
        $this->assertStringContainsString('"ok": true', (string) $result->getBody());
    }
}

