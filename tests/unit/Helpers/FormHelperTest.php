<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FormHelperTest extends CIUnitTestCase
{
    public function testNormalizeMediaReferenceValueInfersExternalUrl(): void
    {
        helper('form');

        $value = normalize_media_reference_value([
            'url' => 'https://cdn.example.com/image.jpg',
        ]);

        $this->assertSame('external_url', $value['source_kind']);
        $this->assertSame('', $value['file_id']);
        $this->assertSame('https://cdn.example.com/image.jpg', $value['url']);
    }

    public function testNormalizeMediaReferenceValueKeepsLegacyFileIds(): void
    {
        helper('form');

        $value = normalize_media_reference_value(null, '42', '/files/42/view');

        $this->assertSame('hub_file', $value['source_kind']);
        $this->assertSame('42', $value['file_id']);
        $this->assertSame('/files/42/view', $value['url']);
    }
}
