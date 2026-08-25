<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\ContentSecurityPolicy;
use Config\Security;

/**
 * @internal
 */
final class SecurityConfigTest extends CIUnitTestCase
{
    public function testCsrfTokenDoesNotRegenerateOnEveryPost(): void
    {
        $config = new Security();

        $this->assertFalse($config->regenerate);
    }

    public function testContentSecurityPolicyAllowsAlpineAndApiFileOrigins(): void
    {
        putenv('apiClient.baseUrl=https://api.example.test/uploads');
        $_ENV['apiClient.baseUrl'] = 'https://api.example.test/uploads';
        putenv('API_BASE_URL=https://api.example.test/uploads');
        $_ENV['API_BASE_URL'] = 'https://api.example.test/uploads';

        try {
            $config = new ContentSecurityPolicy();

            $this->assertFalse($config->autoNonce);
            $this->assertContains("'unsafe-eval'", $config->scriptSrc);
            $this->assertContains("'unsafe-inline'", $config->styleSrc);
            $this->assertContains('https://api.example.test', $config->imageSrc);
        } finally {
            putenv('apiClient.baseUrl');
            unset($_ENV['apiClient.baseUrl']);
            putenv('API_BASE_URL');
            unset($_ENV['API_BASE_URL']);
        }
    }
}
