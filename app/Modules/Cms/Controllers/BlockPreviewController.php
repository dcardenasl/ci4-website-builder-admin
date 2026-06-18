<?php

declare(strict_types=1);

namespace App\Modules\Cms\Controllers;

use App\Controllers\BaseWebController;
use CodeIgniter\HTTP\ResponseInterface;

class BlockPreviewController extends BaseWebController
{
    public function preview(): ResponseInterface
    {
        $blockKeyRaw = $this->request->getPost('block_key');
        $configRaw   = $this->request->getPost('block_config');
        $dataRaw     = $this->request->getPost('block_data');
        $blockKey    = is_scalar($blockKeyRaw) ? (string) $blockKeyRaw : '';
        $configRaw   = is_scalar($configRaw) ? (string) $configRaw : '';
        $dataRaw     = is_scalar($dataRaw) ? (string) $dataRaw : '';

        $config = json_decode($configRaw ?: '{}', true) ?? [];
        $data   = json_decode($dataRaw ?: '{}', true) ?? [];

        $safeKey  = preg_replace('/[^a-z0-9_]/', '', strtolower($blockKey));
        $viewPath = "cms/block_types/previews/{$safeKey}";
        if (! is_file(APPPATH . "Views/{$viewPath}.php")) {
            $viewPath = 'cms/block_types/previews/unknown';
        }

        $html = view($viewPath, compact('config', 'data', 'blockKey'));

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode(['html' => $html]) ?: '{}');
    }
}
