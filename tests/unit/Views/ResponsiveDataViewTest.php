<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ResponsiveDataViewTest extends CIUnitTestCase
{
    public function testSharedTableBoundariesAllowNarrowLayoutsToScrollLocally(): void
    {
        $this->assertStringContainsString('min-w-0', table_wrapper_class());
        $this->assertStringContainsString('min-w-0', table_scroll_class());
        $this->assertStringContainsString('overflow-x-auto', table_scroll_class());
    }

    public function testComplexDataSurfacesOptIntoLocalWidthContainment(): void
    {
        $sources = [
            APPPATH . 'Views/analytics/index.php',
            APPPATH . 'Views/metrics/index.php',
            APPPATH . 'Views/files/partials/list_section.php',
            APPPATH . 'Views/components/time_series_chart.php',
        ];
        $containedSources = [
            APPPATH . 'Views/analytics/index.php',
            APPPATH . 'Views/files/partials/list_section.php',
            APPPATH . 'Views/components/time_series_chart.php',
        ];

        foreach ($sources as $path) {
            $source = file_get_contents($path);

            $this->assertIsString($source, "Unable to read {$path}.");
            $this->assertStringContainsString('table_scroll_class()', $source, "Missing local table scroll boundary in {$path}.");
        }

        foreach ($containedSources as $path) {
            $source = file_get_contents($path);

            $this->assertIsString($source, "Unable to read {$path}.");
            $this->assertStringContainsString('min-w-0', $source, "Missing narrow-layout containment in {$path}.");
        }
    }
}
