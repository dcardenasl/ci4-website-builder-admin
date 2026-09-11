<?php

declare(strict_types=1);

namespace App\Support\TimeSeries;

use InvalidArgumentException;

final readonly class TimeSeriesChartDTO
{
    public const READY = 'ready';
    public const EMPTY = 'empty';
    public const ERROR = 'error';

    /** @param list<TimeSeriesSeriesDTO> $series */
    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public string $state,
        public array $series,
        public TimeSeriesTableMetadataDTO $table,
        public string $message = '',
    ) {
        if (trim($this->id) === '' || trim($this->title) === '' || trim($this->description) === '') {
            throw new InvalidArgumentException('A time-series chart requires accessible identity text.');
        }

        if (! in_array($this->state, [self::READY, self::EMPTY, self::ERROR], true)) {
            throw new InvalidArgumentException('A time-series chart has an unknown state.');
        }

        if (! array_is_list($this->series)) {
            throw new InvalidArgumentException('Chart series must be a list.');
        }

        foreach ($this->series as $series) {
            if (! $series instanceof TimeSeriesSeriesDTO) {
                throw new InvalidArgumentException('A chart can only contain typed series.');
            }
        }

        if ($this->state === self::READY && $this->series === []) {
            throw new InvalidArgumentException('A ready chart requires at least one series.');
        }

        if ($this->state !== self::READY && $this->series !== []) {
            throw new InvalidArgumentException('Empty and error charts cannot contain data series.');
        }

        if (count($this->table->valueHeaders) !== count($this->series) && $this->state === self::READY) {
            throw new InvalidArgumentException('Chart series and table value headers must be aligned.');
        }

        if ($this->state === self::ERROR && trim($this->message) === '') {
            throw new InvalidArgumentException('An error chart requires a user-facing message.');
        }

        if ($this->state === self::READY) {
            $this->assertSeriesAlignment();
        }
    }

    /** @param list<TimeSeriesSeriesDTO> $series */
    public static function ready(
        string $id,
        string $title,
        string $description,
        array $series,
        TimeSeriesTableMetadataDTO $table,
    ): self {
        return new self($id, $title, $description, self::READY, $series, $table);
    }

    public static function empty(
        string $id,
        string $title,
        string $description,
        TimeSeriesTableMetadataDTO $table,
        string $message,
    ): self {
        return new self($id, $title, $description, self::EMPTY, [], $table, $message);
    }

    public static function error(
        string $id,
        string $title,
        string $description,
        TimeSeriesTableMetadataDTO $table,
        string $message,
    ): self {
        return new self($id, $title, $description, self::ERROR, [], $table, $message);
    }

    private function assertSeriesAlignment(): void
    {
        $reference = $this->series[0]->points;
        foreach (array_slice($this->series, 1) as $series) {
            if (count($series->points) !== count($reference)) {
                throw new InvalidArgumentException('Chart series must contain the same number of points.');
            }

            foreach ($reference as $index => $point) {
                if ($point->label !== $series->points[$index]->label) {
                    throw new InvalidArgumentException('Chart series must use the same point labels.');
                }
            }
        }
    }
}
