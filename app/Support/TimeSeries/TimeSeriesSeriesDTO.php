<?php

declare(strict_types=1);

namespace App\Support\TimeSeries;

use InvalidArgumentException;

final readonly class TimeSeriesSeriesDTO
{
    public const MAX_POINTS = 366;

    /** @param list<TimeSeriesPointDTO> $points */
    public function __construct(
        public string $key,
        public string $label,
        public string $unit,
        public array $points,
    ) {
        if (trim($this->key) === '' || trim($this->label) === '' || trim($this->unit) === '') {
            throw new InvalidArgumentException('A time-series requires key, label and unit.');
        }

        if (! array_is_list($this->points) || count($this->points) > self::MAX_POINTS) {
            throw new InvalidArgumentException('A time-series must contain at most 366 points.');
        }

        foreach ($this->points as $point) {
            if (! $point instanceof TimeSeriesPointDTO) {
                throw new InvalidArgumentException('A time-series can only contain typed points.');
            }
        }
    }
}
