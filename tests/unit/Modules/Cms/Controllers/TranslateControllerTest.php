<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Controllers;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\App as AppConfig;
use Config\Services;

/** @internal */
final class TranslateControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testTranslateUsesConfiguredCurlRequestAndReturnsTranslation(): void
    {
        $response = new Response(new AppConfig());
        $response->setStatusCode(200)->setBody(json_encode([[['Hola']]], JSON_THROW_ON_ERROR));

        $client = $this->createMock(CURLRequest::class);
        $client->expects($this->once())
            ->method('get')
            ->with($this->stringContains('translate.googleapis.com/translate_a/single'))
            ->willReturn($response);
        Services::injectMock('curlrequest', $client);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.pages.read']],
        ])->get('/admin/cms/translate?text=Hello&source_lang=en&target_lang=es');

        $result->assertStatus(200);
        $this->assertSame(['translated' => 'Hola'], json_decode((string) $result->getJSON(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testTranslateRejectsMissingParametersBeforeCallingProvider(): void
    {
        $client = $this->createMock(CURLRequest::class);
        $client->expects($this->never())->method('get');
        Services::injectMock('curlrequest', $client);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.pages.read']],
        ])->get('/admin/cms/translate');

        $result->assertStatus(400);
        $this->assertSame(['error' => 'Missing required parameters.'], json_decode((string) $result->getJSON(), true, 512, JSON_THROW_ON_ERROR));
    }
}
