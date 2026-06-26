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
        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/wizard/config')
            ->willReturn([
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
            ]);

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
    }
}
