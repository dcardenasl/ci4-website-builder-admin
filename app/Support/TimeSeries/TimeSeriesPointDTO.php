<?php

declare(strict_types=1);

namespace App\Support\TimeSeries;

use InvalidArgumentException;

final readonly class TimeSeriesPointDTO
{
    public function __construct(
        public string $label,
        public float $value,
    ) {
        if (trim($this->label) === '') {
            throw new InvalidArgumentException('A time-series point requires a label.');
        }

        if (! is_finite($this->value) || $this->value < 0) {
            throw new InvalidArgumentException('A time-series point requires a finite non-negative value.');
        }
    }
}
