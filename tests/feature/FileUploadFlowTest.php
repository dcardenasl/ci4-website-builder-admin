<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Files\Services\FileApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * Tests for the complete file upload flow.
 *
 * @internal
 */
final class FileUploadFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private array $authSession = [
        'access_token' => 'test-token',
        'user'         => ['id' => 1, 'email' => 'user@test.com', 'permissions' => ['files.read', 'files.write']],
    ];

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function testIndexRendersUploadFormForAuthenticatedUser(): void
    {
        $result = $this->withSession($this->authSession)->get('/files');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('name="file"', $body);
        $this->assertStringContainsString('onFileChange(event)', $body);
        $this->assertStringContainsString(lang('Files.file_ready'), $body);
        $this->assertStringContainsString('file-grid', $body);
        $this->assertStringContainsString('file-type-visual', $body);
        $this->assertStringContainsString('filePresentation(row).theme', $body);
    }

    public function testIndexRedirectsToLoginWithoutSession(): void
    {
        $result = $this->get('/files');
        $result->assertRedirectTo(site_url('login'));
    }

    // ─── Download ─────────────────────────────────────────────────

    public function testDownloadSuccessReturnsBinaryResponse(): void
    {
        $mock = $this->createMock(FileApiService::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('abc-123')
            ->willReturn([
                'ok'          => true,
                'status'      => 200,
                'data'        => ['original_name' => 'test.pdf'],
                'raw'         => '%PDF-1.7 content',
                'headers'     => ['content-type' => 'application/pdf'],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('fileApiService', $mock);

        $result = $this->withSession($this->authSession)->get('/files/abc-123/download');

        $result->assertStatus(200);
        $this->assertStringContainsString('%PDF-1.7 content', $result->getBody());
        $result->assertHeader('Content-Type', 'application/pdf');
    }

    public function testDownloadApiFailureReturnsNotFound(): void
    {
        $mock = $this->createMock(FileApiService::class);
        $mock->method('get')->willReturn([
            'ok' => false,
            'status' => 404,
            'data' => [],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ]);

        Services::injectMock('fileApiService', $mock);

        $result = $this->withSession($this->authSession)->get('/files/not-found/download');

        $result->assertStatus(404);
        $this->assertStringContainsString('File not found', $result->getBody());
    }

    // ─── FileApiService unit tests ────────────────────────────────

    public function testFileApiServiceUploadUsesMultipart(): void
    {
        $tmpFile = $this->createTempFile('hello', 'test.txt');

        $mockClient = $this->createMock(\App\Libraries\ApiClientInterface::class);
        $mockClient->expects($this->once())
            ->method('upload')
            ->with(
                '/files/upload',
                $this->callback(function ($files) use ($tmpFile) {
                    return isset($files['file']) && $files['file']['path'] === $tmpFile && $files['file']['filename'] === 'test.txt';
                }),
                []
            )
            ->willReturn($this->apiOkResponse(['id' => 1], 201));

        $mockDomainClient = $this->createMock(\App\Libraries\DomainApiClientInterface::class);
        $mockDomainClient->method('get')->willReturn($this->apiOkResponse([]));

        $service = new FileApiService($mockClient, $mockDomainClient);
        $result = $service->upload('file', $tmpFile, 'test.txt', 'text/plain', []);

        $this->assertTrue($result['ok']);
        @unlink($tmpFile);
    }

    public function testFileDetailsDefersUsageVerificationUntilTheClientFetchesIt(): void
    {
        $mock = $this->createMock(FileApiService::class);
        $mock->expects($this->once())
            ->method('getInfo')
            ->with('7')
            ->willReturn($this->apiOkResponse([
                'id'             => 7,
                'original_name'  => 'image.jpg',
                'variants'       => [],
            ]));
        $mock->expects($this->never())->method('usages');
        Services::injectMock('fileApiService', $mock);

        $result = $this->withSession($this->authSession)->get('/files/7/show');

        $result->assertStatus(200);
        $body = html_entity_decode($result->getBody(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertStringContainsString('data-file-usages', $body);
        $this->assertStringNotContainsString('action="/files/7/delete"', $body);
    }

    public function testFileUsagesEndpointDecoratesUsagesForTheDeferredView(): void
    {
        $mock = $this->createMock(FileApiService::class);
        $mock->expects($this->once())
            ->method('usages')
            ->with('7')
            ->willReturn($this->apiOkResponse([
                'complete' => true,
                'data'     => [[
                    'resource'    => 'pages',
                    'resource_id' => 12,
                    'role'        => 'hero',
                    'label'       => 'Home',
                ]],
            ]));
        Services::injectMock('fileApiService', $mock);

        $result = $this->withSession($this->authSession)->get('/files/7/usages');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('"complete": true', $body);
        $this->assertStringContainsString('/admin/cms/pages/12/edit', $body);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function createTempFile(string $content, string $name): string
    {
        $path = WRITEPATH . $name;
        file_put_contents($path, $content);
        return $path;
    }

    private function apiOkResponse(array $data, int $status = 200): array
    {
        return [
            'ok'          => true,
            'status'      => $status,
            'data'        => $data,
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }
}
