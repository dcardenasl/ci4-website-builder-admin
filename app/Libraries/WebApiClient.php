<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\WebApiClient as WebApiClientConfig;

/**
 * HTTP client targeting the public website (ci4-website-builder-web).
 *
 * Reuses all behaviour of {@see ApiClient} but reads from the `WebApiClient`
 * config so the admin can monitor the public site's health independently.
 * Token refresh is delegated to the Hub client via {@see SecondaryApiClient}
 * — the public web app never exposes its own `/auth/refresh` endpoint.
 */
class WebApiClient extends SecondaryApiClient implements WebApiClientInterface
{
    public function __construct(?WebApiClientConfig $config = null, ?ApiClientInterface $hubClient = null)
    {
        parent::__construct($config ?? config(WebApiClientConfig::class), $hubClient ?? service('apiClient'));
    }
}
