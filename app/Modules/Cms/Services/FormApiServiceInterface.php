<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

interface FormApiServiceInterface
{
    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function list(array $params = []): array;

    /**
     * @return array<string, mixed>
     */
    public function get(int|string $id): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int|string $id, array $data): array;

    /**
     * @return array<string, mixed>
     */
    public function delete(int|string $id): array;

    /**
     * @return array<string, mixed>
     */
    public function getFields(int|string $formId): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createField(int|string $formId, array $data): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateField(int|string $formId, int|string $fieldId, array $data): array;

    /**
     * @return array<string, mixed>
     */
    public function deleteField(int|string $formId, int|string $fieldId): array;

    /**
     * @param list<int> $orderedIds
     * @return array<string, mixed>
     */
    public function reorderFields(int|string $formId, array $orderedIds): array;
}
