<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class BlockEditViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testEditViewKeepsOldInputAndShowsNestedFieldErrors(): void
    {
        helper(['form']);

        service('session')->set('_ci_old_input', [
            'post' => [
                'block_id' => '5',
                'sort_order' => '9',
                'is_active' => '0',
                'block_config' => [
                    'headline' => 'Submitted Headline',
                ],
                'translations' => [
                    [
                        'language_id' => '1',
                        'is_published' => '1',
                        'block_data' => [
                            'title' => 'Submitted Title',
                            'cover_file_id' => '42',
                            'cover_url' => '/files/42/view',
                        ],
                    ],
                ],
            ],
        ]);
        service('session')->set('fieldErrors', [
            'block_config.headline' => 'Headline is required',
            'translations.0.block_data.title' => 'Title is required',
        ]);

        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => ['headline' => 'Existing Headline'],
                'translations' => [
                    [
                        'language_id' => 1,
                        'block_data' => [
                            'title' => 'Existing Title',
                            'cover_file_id' => '42',
                            'cover_url' => '/files/42/view',
                        ],
                    ],
                ],
            ],
            'blockType' => [
                'block_key' => 'hero',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                        'required' => true,
                    ],
                    'cover' => [
                        'type' => 'file',
                        'label' => 'Cover',
                        'accept' => 'image',
                        'required' => false,
                    ],
                ],
                'config_fields' => [
                    'headline' => [
                        'type' => 'string',
                        'label' => 'Headline',
                    ],
                ],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'en',
                ],
            ],
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString('Submitted Headline', $html);
        $this->assertStringContainsString('Submitted Title', $html);
        $this->assertStringContainsString('Headline is required', $html);
        $this->assertStringContainsString('Title is required', $html);
        $this->assertStringContainsString('id="block-edit-form"', $html);
        $this->assertStringContainsString('data-language-id="1"', $html);
        $this->assertStringContainsString('translatableFileField(', $html);
        $this->assertStringContainsString('window.openBlockEditPreview', $html);
    }

    public function testEditViewUsesDefaultLanguageCodeForAutoTranslate(): void
    {
        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [],
                'translations' => [],
            ],
            'blockType' => [
                'block_key' => 'hero',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                ],
                'config_fields' => [],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'pt',
                ],
                [
                    'id' => 2,
                    'is_default' => false,
                    'code' => 'en',
                ],
            ],
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString("langTabs(1, '", $html);
        $this->assertStringContainsString("', 'PT')", $html);
        $this->assertStringNotContainsString("', 'ES')", $html);
    }
}
