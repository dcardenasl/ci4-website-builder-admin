<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\BlockTypeOptionsResolver;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BlockTypeOptionsResolverTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cache()->delete('cms_block_types_resolved_catalog');
    }

    protected function tearDown(): void
    {
        cache()->delete('cms_block_types_resolved_catalog');
        parent::tearDown();
    }

    public function testRawIndexedDoesNotHydrateDynamicOptions(): void
    {
        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn([
            1 => [
                'id' => 1,
                'name' => 'Collection grid',
                'block_key' => 'collection_grid',
                'schema_definition' => ['config_fields' => ['collection_id' => ['type' => 'text']]],
            ],
        ]);

        $formApiClient = $this->createMock(ApiClientInterface::class);
        $formApiClient->expects($this->never())->method('get');
        $collectionApiClient = $this->createMock(ApiClientInterface::class);
        $collectionApiClient->expects($this->never())->method('get');
        $pageApiClient = $this->createMock(ApiClientInterface::class);
        $pageApiClient->expects($this->never())->method('get');
        $entryApiClient = $this->createMock(ApiClientInterface::class);
        $entryApiClient->expects($this->never())->method('get');

        $result = $this->makeResolver($catalog, $formApiClient, $collectionApiClient, $pageApiClient, $entryApiClient)->rawIndexed();

        $this->assertSame('Collection grid', $result[1]['name']);
        $this->assertArrayNotHasKey('options', $result[1]['schema_definition']['config_fields']['collection_id']);
    }

    public function testResolveFetchesCollectionsOnceAcrossManyBlockTypes(): void
    {
        $blockTypes = [];
        for ($i = 1; $i <= 10; $i++) {
            $blockTypes[$i] = [
                'id' => $i,
                'block_key' => 'collection_grid_' . $i,
                'schema_definition' => ['config_fields' => ['collection_id' => ['type' => 'text']]],
            ];
        }

        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn($blockTypes);

        $collectionApiClient = $this->createMock(ApiClientInterface::class);
        $collectionApiClient->expects($this->once())->method('get')->willReturn($this->okResponse([
            ['id' => 1, 'collection_key' => 'news', 'name' => 'News'],
        ]));

        $resolver = $this->makeResolver(
            $catalog,
            $this->createMock(ApiClientInterface::class),
            $collectionApiClient,
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
        );

        $resolved = $resolver->resolve();

        $this->assertCount(10, $resolved);
        $this->assertSame(
            [['value' => 1, 'label' => 'News']],
            $resolved[1]['schema_definition']['config_fields']['collection_id']['options']
        );
    }

    public function testResolveFetchesFormsOnceAcrossManyFormBlocks(): void
    {
        $blockTypes = [];
        for ($i = 1; $i <= 5; $i++) {
            $blockTypes[$i] = [
                'id' => $i,
                'block_key' => 'form_embed',
                'schema_definition' => ['config_fields' => ['form_key' => ['type' => 'text']]],
            ];
        }

        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn($blockTypes);
        $formApiClient = $this->createMock(ApiClientInterface::class);
        $formApiClient->expects($this->once())->method('get')->willReturn($this->okResponse([
            ['form_key' => 'contact'],
        ]));

        $resolver = $this->makeResolver(
            $catalog,
            $formApiClient,
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
        );

        $resolved = $resolver->resolve();

        foreach ($resolved as $blockType) {
            $this->assertSame(['contact'], $blockType['schema_definition']['config_fields']['form_key']['options']);
        }
    }

    public function testResolveUsesHydratedCatalogCacheWhenAvailable(): void
    {
        cache()->save('cms_block_types_resolved_catalog', [
            9 => ['id' => 9, 'name' => 'Cached block type', 'block_key' => 'cached_block'],
        ], 120);

        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->expects($this->never())->method('indexed');

        $resolved = $this->makeResolver(
            $catalog,
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
        )->resolve();

        $this->assertSame('Cached block type', $resolved[9]['name']);
    }

    private function makeResolver(
        BlockCatalogServiceInterface $catalog,
        ApiClientInterface $formApiClient,
        ApiClientInterface $collectionApiClient,
        ApiClientInterface $pageApiClient,
        ApiClientInterface $entryApiClient,
    ): BlockTypeOptionsResolver {
        return new BlockTypeOptionsResolver(
            $catalog,
            new FormApiService($formApiClient),
            new CollectionApiService($collectionApiClient),
            new PageApiService($pageApiClient),
            new EntryApiService($entryApiClient),
        );
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return array<string, mixed>
     */
    private function okResponse(array $items): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => ['data' => $items],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
