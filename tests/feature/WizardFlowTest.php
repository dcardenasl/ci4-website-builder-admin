<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\DomainApiClientInterface;
use App\Modules\Cms\Controllers\WizardController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class WizardFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/wizard');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/wizard');

        $result->assertStatus(200);
        $result->assertSee('¿Qué quieres hacer hoy?');
        $result->assertSee('Volver al panel');
        $result->assertSee('Reintentar');
    }

    public function testConfigUnwrapsDomainPayload(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $configResponse = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'status' => 'success',
                'data' => [
                    'languages' => [
                        ['id' => 1, 'code' => 'es', 'is_default' => true],
                    ],
                    'default_lang_id' => 1,
                    'collections' => [
                        ['id' => 1, 'name' => 'Noticias'],
                    ],
                    'pages' => [
                        ['id' => 2, 'title' => 'Inicio'],
                    ],
                    'menus' => [
                        ['id' => 3, 'name' => 'Main'],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];

        $blockTypesResponse = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'items' => [
                    [
                        'id' => 11,
                        'block_key' => 'rich_text',
                        'name' => 'Rich Text',
                        'description' => 'Editor de texto enriquecido',
                        'icon' => 'align-left',
                        'schema_definition' => ['fields' => []],
                        'supports_pages' => true,
                        'supports_entries' => true,
                        'is_container' => false,
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];

        $mock->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($configResponse, $blockTypesResponse);

        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->config();

        $this->assertSame(200, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('collections', $body);
        $this->assertArrayHasKey('pages', $body);
        $this->assertArrayHasKey('menus', $body);
        $this->assertSame(1, $body['default_lang_id']);
        $this->assertCount(1, $body['collections']);
        $this->assertCount(1, $body['pages']);
        $this->assertCount(1, $body['menus']);
        $this->assertSame(11, $body['block_types']['rich_text']['id']);
        $this->assertTrue($body['block_types']['rich_text']['supports_entries']);
    }

    public function testEntryBlocksUnwrapsDomainPayload(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/entries/7/blocks', ['include_translations' => 1, 'limit' => 100])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'items' => [
                        ['id' => 99, 'block_config' => ['block_key' => 'rich_text']],
                    ],
                    'raw' => '',
                    'headers' => [],
                    'messages' => [],
                    'fieldErrors' => [],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->entryBlocks(7);

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('items', $body);
        $this->assertSame(99, $body['items'][0]['id']);
    }
}
