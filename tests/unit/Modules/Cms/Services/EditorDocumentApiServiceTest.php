<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Services;

use App\Libraries\DomainApiClientInterface;
use App\Modules\Cms\Services\EditorDocumentApiService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class EditorDocumentApiServiceTest extends CIUnitTestCase
{
    public function testLoadMapsDomainEnvelopeToDocumentData(): void
    {
        $client = $this->createMock(DomainApiClientInterface::class);
        $client->expects($this->once())
            ->method('get')
            ->with('/cms/editor/pages/7/document')
            ->willReturn($this->response(['owner' => ['id' => 7], 'version' => 'v1']));

        $service = new EditorDocumentApiService($client);
        $result = $service->load('page', 7);

        $this->assertTrue($result['ok']);
        $this->assertSame(['owner' => ['id' => 7], 'version' => 'v1'], $result['data']);
    }

    public function testPatchKeepsConflictStatusAndDoesNotRetry(): void
    {
        $client = $this->createMock(DomainApiClientInterface::class);
        $client->expects($this->once())
            ->method('post')
            ->with('/cms/editor/entries/9/document', ['base_version' => 'stale', 'ops' => []])
            ->willReturn([
                'ok' => false,
                'status' => 409,
                'data' => ['data' => ['code' => 'version_conflict']],
                'raw' => '',
                'headers' => [],
                'messages' => ['version_conflict'],
                'fieldErrors' => [],
            ]);

        $result = (new EditorDocumentApiService($client))->patch('entry', 9, [
            'base_version' => 'stale',
            'ops' => [],
        ]);

        $this->assertFalse($result['ok']);
        $this->assertSame(409, $result['status']);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function response(array $data): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => ['status' => 'success', 'data' => $data],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
