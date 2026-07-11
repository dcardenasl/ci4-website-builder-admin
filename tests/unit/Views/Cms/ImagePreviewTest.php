<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The `image` block's `image` field resolves server-side to `image_file_id` +
 * `image_url` (the same file-field convention `hero_banner`'s preview already
 * follows) — the fallback preview used to read the wrong key (`url`) and
 * silently always fell back to the placeholder. See H-010,
 * ../../../../docs/audits/2026-07-10-auditoria-profunda-robustez.md.
 *
 * @internal
 */
final class ImagePreviewTest extends CIUnitTestCase
{
    public function testPreviewRendersTheResolvedImageUrlNotAPlaceholder(): void
    {
        $html = view('cms/block_types/previews/image', [
            'config' => [],
            'data' => [
                'image_url' => 'https://cdn.example.com/photos/real-photo.jpg',
                'alt' => 'A real photo',
                'caption' => 'A real caption',
            ],
        ]);

        $this->assertStringContainsString('https://cdn.example.com/photos/real-photo.jpg', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
    }

    public function testPreviewFallsBackToPlaceholderWhenNoImageUrlIsResolvedYet(): void
    {
        $html = view('cms/block_types/previews/image', [
            'config' => [],
            'data' => [],
        ]);

        $this->assertStringContainsString('placehold.co', $html);
    }
}
