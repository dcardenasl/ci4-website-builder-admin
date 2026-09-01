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

    public function testInlineSubmitKeepsTheSharedPanelOptIn(): void
    {
        $defaultHtml = view('layouts/partials/filter_panel', [
            'fieldsView' => 'analytics/partials/filters',
        ]);
        $inlineHtml = view('layouts/partials/filter_panel', [
            'fieldsView'   => 'analytics/partials/filters',
            'submitInline' => true,
        ]);

        $this->assertStringNotContainsString('md:flex-row md:items-end', $defaultHtml);
        $this->assertStringContainsString('md:flex-row md:items-end', $inlineHtml);
    }

    public function testPanelClassCanBeCustomizedWithoutChangingTheDefault(): void
    {
        $defaultHtml = view('layouts/partials/filter_panel');
        $customHtml = view('layouts/partials/filter_panel', [
            'panelClass' => 'custom-filter-panel',
        ]);

        $this->assertStringContainsString('mt-4 rounded-xl border border-gray-200 bg-white p-4', $defaultHtml);
        $this->assertStringContainsString('class="custom-filter-panel"', $customHtml);
    }
}
