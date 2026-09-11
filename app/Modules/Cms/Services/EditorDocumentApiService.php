<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Libraries\DomainApiClientInterface;

/**
 * Adapter for the Domain editor contract.
 *
 * The Admin never interprets block trees or patches. It only normalizes the
 * Domain API envelope so the controller and browser consume one stable shape.
 */
final class EditorDocumentApiService
{
    public function __construct(private readonly DomainApiClientInterface $client)
    {
    }

    /** @return array<string, mixed> */
    public function load(string $ownerType, int $ownerId): array
    {
        return $this->normalize($this->client->get($this->path($ownerType, $ownerId)));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function patch(string $ownerType, int $ownerId, array $payload): array
    {
        return $this->normalize($this->client->post($this->path($ownerType, $ownerId), $payload));
    }

    /** @return array<string, mixed> */
    public function publish(string $ownerType, int $ownerId): array
    {
        $response = $ownerType === 'entry'
            ? $this->client->post('/cms/entries/' . $ownerId . '/publish')
            : $this->client->put('/cms/pages/' . $ownerId, ['status' => 'published']);

        return $this->normalize($response);
    }

    public function maxPayloadBytes(): int
    {
        $configured = env('EDITOR_DOCUMENT_MAX_PAYLOAD_BYTES');
        $value = is_numeric($configured) ? (int) $configured : 1048576;

        return max(1024, min($value, 5242880));
    }

    private function path(string $ownerType, int $ownerId): string
    {
        $resource = $ownerType === 'entry' ? 'entries' : 'pages';

        return '/cms/editor/' . $resource . '/' . $ownerId . '/document';
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function normalize(array $response): array
    {
        if (! ($response['ok'] ?? false)) {
            return $response + ['data' => []];
        }

        $payload = $response['data'] ?? [];
        if (! is_array($payload)) {
            return $response + ['data' => []];
        }

        $data = $payload['data'] ?? $payload;
        $messages = $payload['messages'] ?? ($response['messages'] ?? []);
        $fieldErrors = $payload['fieldErrors'] ?? ($response['fieldErrors'] ?? []);

        return [
            'ok' => true,
            'status' => (int) ($response['status'] ?? 200),
            'data' => is_array($data) ? $data : [],
            'raw' => (string) ($response['raw'] ?? ''),
            'headers' => is_array($response['headers'] ?? null) ? $response['headers'] : [],
            'messages' => is_array($messages) ? array_values(array_map('strval', $messages)) : [],
            'fieldErrors' => is_array($fieldErrors) ? $fieldErrors : [],
        ];
    }
}
