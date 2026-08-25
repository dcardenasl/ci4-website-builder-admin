<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class CacheFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testPublicCacheScreenRequiresAuthentication(): void
    {
        $result = $this->get('/admin/system/cache');

        $result->assertRedirectTo(site_url('login'));
    }

    public function testPublicCacheScreenRequiresItsReadPermission(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/system/cache');

        $result->assertRedirectTo(site_url('dashboard'));
    }
}
