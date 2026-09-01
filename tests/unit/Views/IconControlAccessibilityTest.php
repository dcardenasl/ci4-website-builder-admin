<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class IconControlAccessibilityTest extends CIUnitTestCase
{
    public function testSharedToolbarTogglesExposeTheCompleteControlContract(): void
    {
        $html = view('layouts/partials/table_toolbar', [
            'title' => 'Example page',
            'showViewToggle' => true,
            'showDensityToggle' => true,
        ], ['saveData' => false]);

        $this->assertStringContainsString('role="group" aria-label="Vista de tabla"', $html);
        $this->assertStringContainsString('title="Tabla"', $html);
        $this->assertStringContainsString('aria-label="Tabla"', $html);
        $this->assertStringContainsString(':aria-pressed="viewMode === \'table\'"', $html);
        $this->assertStringContainsString('min-h-10 min-w-10', $html);
        $this->assertStringContainsString('focus-visible:ring-2', $html);
    }

    public function testFileViewTogglesAreGroupedAndNamed(): void
    {
        $source = file_get_contents(APPPATH . 'Views/files/partials/list_section.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("role=\"group\" aria-label=\"<?= esc(lang('App.table_view')) ?>\"", $source);
        $this->assertSame(2, substr_count($source, "aria-label=\"<?= esc(lang('App.view_"));
        $this->assertSame(2, substr_count($source, "title=\"<?= esc(lang('App.view_"));
        $this->assertSame(2, substr_count($source, ':aria-pressed="viewMode ==='));
        $this->assertStringContainsString('focus-visible:ring-2', $source);
        $this->assertStringContainsString('min-h-10 min-w-10', $source);
    }
}
