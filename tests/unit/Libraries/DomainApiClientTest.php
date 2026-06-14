<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\ApiClient;
use App\Libraries\ApiClientInterface;
use App\Libraries\DomainApiClient;
use App\Libraries\DomainApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\DomainApiClient as DomainApiClientConfig;
use Config\Services;

/**
 * @internal
 */
final class DomainApiClientTest extends CIUnitTestCase
{
    public function testClassImplementsBothInterfaces(): void
    {
        $reflection = new \ReflectionClass(DomainApiClient::class);
        $this->assertTrue($reflection->implementsInterface(DomainApiClientInterface::class));
        $this->assertTrue($reflection->implementsInterface(ApiClientInterface::class));
    }

    public function testExtendsApiClient(): void
    {
        $this->assertTrue(is_subclass_of(DomainApiClient::class, ApiClient::class));
    }

    public function testDomainInterfaceExtendsApiInterface(): void
    {
        $reflection = new \ReflectionClass(DomainApiClientInterface::class);
        $this->assertTrue($reflection->isSubclassOf(ApiClientInterface::class));
    }

    public function testConfigDefaultBaseUrlPointsToPort8090(): void
    {
        // Save and clear the env var so we test the actual default
        $saved = $_ENV['domainApiClient.baseUrl'] ?? null;
        unset($_ENV['domainApiClient.baseUrl']);

        $config = new DomainApiClientConfig();
        $this->assertSame('http://localhost:8090', $config->baseUrl);

        // Restore the env var if it was set
        if ($saved !== null) {
            $_ENV['domainApiClient.baseUrl'] = $saved;
        }
    }

    public function testConfigInheritsApiClientDefaults(): void
    {
        $config = new DomainApiClientConfig();
        $this->assertSame(15, $config->timeout);
        $this->assertSame(5, $config->connectTimeout);
        $this->assertSame('/api/v1', $config->apiPrefix);
    }

    public function testConfigBaseUrlOverridableViaEnv(): void
    {
        // Save and clear the dotted key so we test the UPPERCASE key override
        $saved = $_ENV['domainApiClient.baseUrl'] ?? null;
        unset($_ENV['domainApiClient.baseUrl']);

        $_ENV['DOMAIN_API_BASE_URL'] = 'http://localhost:9999';
        $config = new DomainApiClientConfig();
        $this->assertSame('http://localhost:9999', $config->baseUrl);
        unset($_ENV['DOMAIN_API_BASE_URL']);

        // Restore the env var if it was set
        if ($saved !== null) {
            $_ENV['domainApiClient.baseUrl'] = $saved;
        }
    }

    public function testConfigDottedKeyOverridesUppercase(): void
    {
        $_ENV['domainApiClient.baseUrl'] = 'http://dotted.example';
        $_ENV['DOMAIN_API_BASE_URL']     = 'http://uppercase.example';
        $config = new DomainApiClientConfig();
        $this->assertSame('http://dotted.example', $config->baseUrl);
        unset($_ENV['domainApiClient.baseUrl'], $_ENV['DOMAIN_API_BASE_URL']);
    }

    public function testConfigDoesNotReadApiClientHubEnvVars(): void
    {
        // Hub env vars must NOT leak into the domain config — otherwise both
        // clients would silently point at the same backend.
        // Save and clear the domain client key so we test the default behavior
        $saved = $_ENV['domainApiClient.baseUrl'] ?? null;
        unset($_ENV['domainApiClient.baseUrl']);

        $_ENV['API_BASE_URL']     = 'http://hub.example';
        $_ENV['apiClient.baseUrl'] = 'http://hub.example';

        $config = new DomainApiClientConfig();
        $this->assertSame('http://localhost:8090', $config->baseUrl);

        unset($_ENV['API_BASE_URL'], $_ENV['apiClient.baseUrl']);

        // Restore the env var if it was set
        if ($saved !== null) {
            $_ENV['domainApiClient.baseUrl'] = $saved;
        }
    }

    public function testServicesFactoryReturnsDomainApiClientInterface(): void
    {
        $instance = Services::domainApiClient(false);
        $this->assertInstanceOf(DomainApiClientInterface::class, $instance);
        $this->assertInstanceOf(DomainApiClient::class, $instance);
    }

    public function testServicesFactoryAndApiClientAreDistinct(): void
    {
        $hub    = Services::apiClient(false);
        $domain = Services::domainApiClient(false);

        $this->assertNotSame($hub, $domain);
        $this->assertInstanceOf(DomainApiClientInterface::class, $domain);
        // Domain client must satisfy ApiClientInterface so existing services accept it.
        $this->assertInstanceOf(ApiClientInterface::class, $domain);
    }
}
