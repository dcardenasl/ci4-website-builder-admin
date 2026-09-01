<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class LayoutAssetSecurityTest extends CIUnitTestCase
{
    public function testAdminLayoutLoadsJavaScriptOnlyFromLocalAssets(): void
    {
        $source = file_get_contents(APPPATH . 'Views/layouts/partials/head.php');
        $this->assertIsString($source);
        $this->assertStringNotContainsString('jsdelivr', $source);
        $this->assertStringNotContainsString('unpkg', $source);
        $this->assertStringNotContainsString('cdnjs', $source);
        $this->assertStringContainsString("assets/vendor/alpine.min.js", $source);
        $this->assertStringContainsString("assets/vendor/lucide.min.js", $source);
        $this->assertStringContainsString("assets/vendor/sortable.min.js", $source);
        $this->assertStringContainsString("assets/vendor/tiptap.bundle.js", $source);
    }
}
