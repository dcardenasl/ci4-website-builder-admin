<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class FilterPanelViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testNonReactivePanelsDoNotRenderAlpineBindings(): void
    {
        $html = view('layouts/partials/filter_panel', [
            'hasFilters'         => true,
            'reactiveHasFilters' => false,
        ]);

        $this->assertStringNotContainsString('x-show="loading"', $html);
        $this->assertStringNotContainsString('x-show="hasActiveFilters()"', $html);
    }

    public function testReactivePanelsRenderTheirLoadingAndChipBindings(): void
    {
        $html = view('layouts/partials/filter_panel', [
            'hasFilters'         => true,
            'reactiveHasFilters' => true,
        ]);

        $this->assertStringContainsString('x-show="loading"', $html);
        $this->assertStringContainsString('x-show="hasActiveFilters()"', $html);
    }
}
