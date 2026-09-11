<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\DomainApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/** @internal */
final class EditorFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testEditorRequiresAuthentication(): void
    {
        $this->get('/admin/cms/editor/pages/1')->assertRedirectTo(site_url('login'));
    }

    public function testEditorLoadsDocumentThroughDomainAdapter(): void
    {
        $client = $this->createMock(DomainApiClientInterface::class);
        $client->expects($this->once())
            ->method('get')
            ->with('/cms/editor/pages/1/document')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['status' => 'success', 'data' => [
                    'owner' => ['id' => 1, 'title' => '<Safe title>'],
                    'version' => str_repeat('a', 64),
                    'locales' => [],
                    'blocks' => [],
                    'catalog' => [],
                ]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $client);

        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user' => ['permissions' => ['cms.pages.read', 'cms.pages.write']],
        ])->get('/admin/cms/editor/pages/1');

        $result->assertStatus(200);
        $this->assertStringContainsString('data-editor-owner-type="page"', $result->getBody());
        $this->assertStringNotContainsString('<Safe title>', $result->getBody());
        $this->assertStringContainsString('window.__canvasBoot', $result->getBody());
    }
}
