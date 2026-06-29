<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class SettingsFiltersViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testSettingsFiltersDoNotAdvertiseDeadAutoApplyContract(): void
    {
        $html = view('cms/settings/partials/filters', [
            'limitOptions' => [10, 25, 50, 100],
        ]);

        $this->assertStringContainsString('name="setting_group"', $html);
        $this->assertStringNotContainsString('data-table-filter', $html);
    }
}
