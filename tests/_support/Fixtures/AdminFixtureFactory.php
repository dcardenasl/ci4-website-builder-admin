<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

final class AdminFixtureFactory
{
    private int $sequence = 0;

    private string $scope;

    public function __construct(string $scope = 'case')
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '-', strtolower($scope)) ?? 'case';
        $this->scope = substr(trim($normalized, '-'), 0, 16) ?: 'case';
    }

    /** @return list<array{id:int,code:string,label:string,is_default:bool}> */
    public function languages(int $count = 3, int $defaultPosition = 0): array
    {
        $languages = [];
        for ($position = 0; $position < $count; $position++) {
            $languages[] = [
                'id' => 100 + $position,
                'code' => 'l' . str_pad((string) ($position + 1), 2, '0', STR_PAD_LEFT),
                'label' => $this->makeValue('language', (string) ($position + 1)),
                'is_default' => $position === $defaultPosition,
            ];
        }

        return $languages;
    }

    /** @param list<array<string, mixed>> $translations */
    public function collection(array $translations): array
    {
        return [
            'id' => 1000 + ++$this->sequence,
            'collection_key' => $this->makeValue('collection-key'),
            'translations' => $translations,
        ];
    }

    public function value(string $role, string $variant = ''): string
    {
        return $this->makeValue($role, $variant);
    }

    /** @param mixed $data */
    public function response(mixed $data): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => $data,
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }

    private function makeValue(string $role, string $variant = ''): string
    {
        ++$this->sequence;
        $parts = ['fixture', $this->scope, $role];
        if ($variant !== '') {
            $parts[] = $variant;
        }

        $parts[] = (string) $this->sequence;

        return strtolower(implode('-', array_map(
            static fn (string $part): string => trim($part, '-_ '),
            $parts,
        )));
    }
}
