<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HeadingHierarchyTest extends CIUnitTestCase
{
    public function testPageToolbarOwnsThePageLevelHeading(): void
    {
        $html = view('layouts/partials/table_toolbar', [
            'title' => 'Example page',
        ], ['saveData' => false]);

        $this->assertSame(1, substr_count($html, '<h1 '));
        $this->assertStringNotContainsString('<h3 ', $html);
    }

    public function testFilterPanelUsesAFieldsetLegendInsteadOfAHeadingLevelJump(): void
    {
        $html = view('layouts/partials/filter_panel', [
            'actionUrl' => '/',
            'clearUrl' => '/',
            'hasFilters' => false,
        ], ['saveData' => false]);

        $this->assertStringContainsString('<fieldset>', $html);
        $this->assertStringContainsString('<legend', $html);
        $this->assertStringNotContainsString('<h4 ', $html);
    }
}
