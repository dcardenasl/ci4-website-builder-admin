<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Services;

use App\Modules\Cms\Services\BlockCatalogService;
use App\Modules\Cms\Services\BlockTypeApiServiceInterface;
use CodeIgniter\Test\CIUnitTestCase;

final class BlockCatalogServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cache()->delete('cms_block_types_active_catalog');
    }

    public function testReturnsEmptyCatalogWhenDomainIsUnavailable(): void
    {
        $blockTypes = $this->createMock(BlockTypeApiServiceInterface::class);
        $blockTypes->method('list')->willThrowException(new \RuntimeException('Domain unavailable'));

        $this->assertSame([], (new BlockCatalogService($blockTypes))->all());
    }
}
