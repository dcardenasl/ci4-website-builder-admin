<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\PublicSiteCacheInvalidator;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/** @internal */
final class PublicSiteCacheInvalidatorTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testMissingConfigurationIsReportedWithoutCallingRemoteSite(): void
    {
        $client = $this->createMock(CURLRequest::class);
        $client->expects($this->never())->method('request');
        Services::injectMock('curlrequest', $client);

        $result = new PublicSiteCacheInvalidator('', '')->invalidateWithResult(['pages']);

        $this->assertFalse($result['ok']);
        $this->assertSame(0, $result['status']);
        $this->assertSame('Cache invalidation is not configured.', $result['message']);
    }

    public function testInvalidationKeepsRemoteStatusAndPayloadContractOnHttpFailure(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(503);
        $response->method('getBody')->willReturn(json_encode(['message' => 'upstream unavailable'], JSON_THROW_ON_ERROR));

        $client = $this->createMock(CURLRequest::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/cache/invalidate')
            ->willReturn($response);
        Services::injectMock('curlrequest', $client);

        $result = new PublicSiteCacheInvalidator('http://public.test', 'secret')
            ->invalidateWithResult(['pages', 'invalid-scope']);

        $this->assertFalse($result['ok']);
        $this->assertSame(503, $result['status']);
        $this->assertSame('upstream unavailable', $result['message']);
    }
}
