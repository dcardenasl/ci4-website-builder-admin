<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class FormFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/forms');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms');

        $result->assertStatus(200);
        $result->assertSee(lang('Forms.title'));
    }

    public function testShowRendersForAdmin(): void
    {
        $formMock = $this->createMock(FormApiService::class);
        $formMock->method('get')
            ->with('123')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 123,
                    'form_key' => 'contact_us',
                    'is_active' => true,
                    'has_captcha' => false,
                    'notify_email' => 'admin@test.com',
                    'autoreply_enabled' => false,
                    'autoreply_email_field' => null,
                    'created_at' => '2026-06-25 00:00:00',
                    'translations' => [
                        [
                            'language_id' => 1,
                            'name' => 'Contact Us Form',
                            'submit_label' => 'Submit',
                            'description' => 'Send us a message',
                            'success_message' => 'Thanks!',
                            'error_message' => 'Error!'
                        ]
                    ],
                    'fields' => [
                        [
                            'id' => 1,
                            'field_key' => 'email',
                            'field_type' => 'email',
                            'is_required' => true,
                            'translations' => [
                                [
                                    'language_id' => 1,
                                    'label' => 'Your Email',
                                    'placeholder' => 'Enter your email',
                                    'help_text' => 'Must be valid'
                                ]
                            ]
                        ]
                    ]
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'items' => [
                        ['id' => 1, 'code' => 'en', 'name' => 'English', 'is_active' => true]
                    ]
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        Services::injectMock('formApiService', $formMock);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms/123');

        $result->assertStatus(200);
        $result->assertSee('Contact Us Form');
        $result->assertSee('contact_us');
        $result->assertSee('admin@test.com');
        $result->assertSee('Your Email');
    }

    public function testShowRendersLinkedUsagesAndOpenEditorLink(): void
    {
        $formMock = $this->createMock(FormApiService::class);
        $formMock->method('get')
            ->with('123')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 123,
                    'form_key' => 'gdpr_rights',
                    'is_active' => true,
                    'has_captcha' => false,
                    'notify_email' => null,
                    'autoreply_enabled' => false,
                    'autoreply_email_field' => null,
                    'created_at' => '2026-06-25 00:00:00',
                    'translations' => [
                        [
                            'language_id' => 1,
                            'name' => 'GDPR Rights',
                            'submit_label' => 'Submit',
                            'description' => null,
                            'success_message' => 'Thanks!',
                            'error_message' => 'Error!'
                        ]
                    ],
                    'fields' => [],
                    'usages' => [
                        [
                            'resource' => 'block_instances',
                            'resource_id' => 14,
                            'role' => 'page',
                            'label' => 'Contacto',
                            'context' => [
                                'owner_type' => 'page',
                                'owner_id' => 11,
                                'block_key' => 'form_embed',
                                'block_name' => 'Formulario Embebido',
                            ],
                            'edit_url' => 'http://localhost:8182/admin/cms/pages/11/blocks/14/edit',
                        ],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'items' => [
                        ['id' => 1, 'code' => 'es', 'name' => 'Spanish', 'is_active' => true]
                    ]
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => []
            ]);

        Services::injectMock('formApiService', $formMock);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms/123');

        $result->assertStatus(200);
        $result->assertSee(lang('Forms.usages_title'));
        $result->assertSee(lang('Forms.usage_page'));
        $result->assertSee('Contacto');
        $result->assertSee(lang('Forms.usage_edit'));
        $result->assertSee('http://localhost:8182/admin/cms/pages/11/blocks/14/edit');
        $result->assertDontSee('/admin/cms/forms/123/delete');
    }

    public function testShowFormNotFoundRendersError(): void
    {
        $formMock = $this->createMock(FormApiService::class);
        $formMock->method('get')
            ->with('999')
            ->willReturn([
                'ok' => false,
                'status' => 404,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ['Form not found.'],
                'fieldErrors' => []
            ]);

        Services::injectMock('formApiService', $formMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.forms.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/forms/999');

        $result->assertStatus(200);
        $result->assertSee('Form not found.');
    }
}
