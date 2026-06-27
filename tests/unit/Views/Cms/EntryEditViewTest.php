<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class EntryEditViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testEditViewShowsFeaturedImageFieldPerLanguage(): void
    {
        service('request')->setLocale('es');

        $html = view('cms/entries/edit', [
            'item' => [
                'id' => 99,
                'collection_id' => 5,
                'status' => 'draft',
                'workflow_status' => 'draft',
                'is_featured' => false,
                'translations' => [
                    [
                        'language_id' => 1,
                        'slug' => 'entrada-ejemplo',
                        'title' => 'Entrada ejemplo',
                        'excerpt' => 'Un resumen',
                        'featured_file_id' => 42,
                        'featured_image_url' => '/files/42/view',
                    ],
                    [
                        'language_id' => 2,
                        'slug' => 'sample-entry',
                        'title' => 'Sample entry',
                        'excerpt' => 'Summary',
                        'featured_file_id' => 42,
                        'featured_image_url' => '/files/42/view',
                    ],
                ],
            ],
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
            'blockTemplate' => null,
            'translateTargets' => [],
        ]);

        $this->assertStringContainsString('translatableFileField(', $html);
        $this->assertStringContainsString('Copiar a otros idiomas', $html);
        $this->assertStringContainsString('copyFileFieldToTargets(', $html);
        $this->assertStringContainsString('featured_image_url', $html);
    }
}
