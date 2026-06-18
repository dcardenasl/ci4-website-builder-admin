<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HeroSliderPreviewTest extends CIUnitTestCase
{
    public function testPreviewReflectsCarouselLayoutConfig(): void
    {
        $html = view('cms/block_types/previews/hero_slider', [
            'config' => [
                'caption_position' => 'overlay_bottom',
                'controls_position' => 'overlay_bottom',
            ],
            'data' => [
                'slide_1_image_url' => 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%221200%22%20height%3D%22500%22%2F%3E',
                'slide_1_heading' => 'Caption text',
                'slide_1_subtitle' => 'Supporting text',
                'slide_1_cta_label' => 'Learn more',
            ],
        ]);

        $this->assertStringContainsString('data-caption-position="overlay_bottom"', $html);
        $this->assertStringContainsString('data-controls-position="overlay_bottom"', $html);
        $this->assertStringContainsString('data-hero-caption-title', $html);
        $this->assertStringContainsString('Caption text', $html);
        $this->assertStringContainsString('Learn more', $html);
    }
}
