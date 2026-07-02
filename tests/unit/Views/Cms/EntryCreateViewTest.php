<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class EntryCreateViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testCreateViewShowsFeaturedImageCopyControlPerLanguage(): void
    {
        service('request')->setLocale('es');

        $html = view('cms/entries/create', [
            'item' => [],
            'collections' => [
                5 => 'Blog',
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'es',
                ],
                [
                    'id' => 2,
                    'is_default' => false,
                    'code' => 'en',
                ],
            ],
            'defaultLangId' => 1,
            'defaultLangCode' => 'es',
            'defaultLangIndex' => 0,
            'translateTargets' => [],
        ]);

        $this->assertStringContainsString('translatableFileField(', $html);
        $this->assertStringContainsString('Copiar a otros idiomas', $html);
        $this->assertStringContainsString('window.copyLangTabsFileFieldToTargets(', $html);
        $this->assertStringContainsString('featured_image_url', $html);
    }
}
