<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\DomainApiClientInterface;
use App\Services\SortOrderApiService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class SortOrderApiServiceTest extends CIUnitTestCase
{
    public function testCmsPostsOneAtomicBatchToTheResourceEndpoint(): void
    {
        $client = $this->createMock(DomainApiClientInterface::class);
        $expected = [
            'ok' => true,
            'status' => 200,
            'data' => ['updated' => 2],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
        $items = [
            ['id' => 9, 'sort_order' => 0],
            ['id' => 4, 'sort_order' => 1],
        ];
        $scope = ['collection_id' => 7];

        $client->expects($this->once())
            ->method('post')
            ->with('/cms/sort-orders', [
                'resource' => 'entries',
                'items' => $items,
                'scope' => $scope,
            ])
            ->willReturn($expected);

        $this->assertSame($expected, (new SortOrderApiService($client))->cms('entries', $items, $scope));
    }
}
