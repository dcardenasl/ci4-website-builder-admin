<?php

declare(strict_types=1);

namespace App\Support\TimeSeries;

use InvalidArgumentException;

final readonly class TimeSeriesTableMetadataDTO
{
    /** @param list<string> $valueHeaders */
    public function __construct(
        public string $caption,
        public string $labelHeader,
        public array $valueHeaders,
    ) {
        if (trim($this->caption) === '' || trim($this->labelHeader) === '' || $this->valueHeaders === []) {
            throw new InvalidArgumentException('A time-series table requires accessible headers.');
        }

        if (! array_is_list($this->valueHeaders)) {
            throw new InvalidArgumentException('Time-series table headers must be a list.');
        }

        foreach ($this->valueHeaders as $header) {
            if (! is_string($header) || trim($header) === '') {
                throw new InvalidArgumentException('Time-series table headers must be non-empty strings.');
            }
        }
    }
}
