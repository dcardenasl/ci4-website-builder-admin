<?php

declare(strict_types=1);

namespace App\Support;

/** Presentation mode returned by the identity API for the admin shell. */
enum UiMode: string
{
    case Full = 'full';
    case Simple = 'simple';

    public static function fromMixed(mixed $value): self
    {
        return self::tryFrom(strtolower(trim((string) $value))) ?? self::Full;
    }
}
