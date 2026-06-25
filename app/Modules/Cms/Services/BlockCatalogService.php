<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

final class BlockCatalogService implements BlockCatalogServiceInterface
{
    private const CACHE_KEY = 'cms_block_types_active_catalog';

    /**
     * @param array<int, array<string, mixed>>|null $cachedItems
     */
    public function __construct(
        private readonly BlockTypeApiServiceInterface $blockTypeService
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $cachedItems = cache()->get(self::CACHE_KEY);
        if (is_array($cachedItems)) {
            return $cachedItems;
        }

        $response = $this->blockTypeService->list(['limit' => 100, 'is_active' => true]);
        if (! ($response['ok'] ?? false)) {
            return [];
        }

        $items = $this->extractItems($response);
        $items = array_values(array_filter($items, static fn (array $item): bool => self::isActive($item)));
        usort($items, static function (array $a, array $b): int {
            $orderA = (int) ($a['sort_order'] ?? 0);
            $orderB = (int) ($b['sort_order'] ?? 0);
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            $nameA = strtolower((string) ($a['name'] ?? ''));
            $nameB = strtolower((string) ($b['name'] ?? ''));
            if ($nameA !== $nameB) {
                return $nameA <=> $nameB;
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        cache()->save(self::CACHE_KEY, $items, 3600);

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function indexed(): array
    {
        $indexed = [];
        foreach ($this->all() as $type) {
            if (isset($type['id'])) {
                $indexed[(int) $type['id']] = $type;
            }
        }

        return $indexed;
    }

    /**
     * @param array<string, mixed> $response
     * @return array<int, array<string, mixed>>
     */
    private function extractItems(array $response): array
    {
        $payload = $response['data'] ?? [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        return is_array($payload) ? array_values($payload) : [];
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isActive(array $item): bool
    {
        $value = $item['is_active'] ?? false;

        return $value === true || $value === 1 || $value === '1';
    }
}
