<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Fallback previews are rendered while the public site is unavailable, so
 * their visible copy must remain localizable without depending on a remote UI.
 */
final class BlockPreviewLocalizationTest extends CIUnitTestCase
{
    public function testPreviewTemplatesDoNotContainRawVisibleCopy(): void
    {
        $paths = glob(APPPATH . 'Views/cms/block_types/previews/*.php');
        $this->assertIsArray($paths);

        foreach ($paths as $path) {
            $source = file_get_contents($path);
            $this->assertIsString($source, "Unable to read preview template {$path}.");

            $withoutPhp = preg_replace('~<\\?(?:php|=).*?\\?>~s', '', $source);
            $withoutComments = preg_replace('~<!--[\\s\\S]*?-->~', '', $withoutPhp ?? $source);
            $visibleText = trim(preg_replace('~\\s+~', ' ', strip_tags($withoutComments ?? '')) ?? '');

            $this->assertDoesNotMatchRegularExpression(
                '/[A-Za-zÀ-ÿ]{2,}/u',
                $visibleText,
                'Visible copy must use BlockPreview language keys: ' . basename($path)
            );
        }
    }
}
