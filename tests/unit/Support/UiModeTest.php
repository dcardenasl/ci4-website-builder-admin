<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\UiMode;
use PHPUnit\Framework\TestCase;

/** @internal */
final class UiModeTest extends TestCase
{
    public function testOnlyTheKnownSimpleValueSelectsTheSimpleMode(): void
    {
        self::assertSame(UiMode::Simple, UiMode::fromMixed('simple'));
        self::assertSame(UiMode::Full, UiMode::fromMixed('full'));
        self::assertSame(UiMode::Full, UiMode::fromMixed('unknown'));
        self::assertSame(UiMode::Full, UiMode::fromMixed(null));
    }
}
