<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\ResponseInterface;

class TranslateController extends BaseWebController
{
    private const MYMEMORY_URL = 'https://api.mymemory.translated.net/get';

    public function translate(): ResponseInterface
    {
        $text       = (string) ($this->request->getGet('text') ?? '');
        $sourceLang = strtoupper((string) ($this->request->getGet('source_lang') ?? 'EN'));
        $targetLang = strtoupper((string) ($this->request->getGet('target_lang') ?? ''));

        if ($text === '' || $targetLang === '') {
            return $this->response->setJSON(['error' => 'Missing required parameters.'])->setStatusCode(400);
        }

        $url = self::MYMEMORY_URL . '?' . http_build_query([
            'q'        => $text,
            'langpair' => $sourceLang . '|' . $targetLang,
        ]);

        $ch = curl_init($url);
        if ($ch === false) {
            return $this->response->setJSON(['error' => 'Translation service unavailable.'])->setStatusCode(503);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (! is_string($body)) {
            return $this->response->setJSON(['error' => 'No response from translation service.'])->setStatusCode(503);
        }

        $json = json_decode($body, true);

        if ($status !== 200 || ! is_array($json)) {
            return $this->response->setJSON(['error' => 'Translation failed.'])->setStatusCode(502);
        }

        $translated = (string) ($json['responseData']['translatedText'] ?? '');
        if ($translated === '') {
            return $this->response->setJSON(['error' => 'Empty translation response.'])->setStatusCode(502);
        }

        return $this->response->setJSON(['translated' => $translated]);
    }
}
