<?php

declare(strict_types=1);

namespace App\Modules\Metrics\Support;

use App\Support\TimeSeries\TimeSeriesChartDTO;
use App\Support\TimeSeries\TimeSeriesPointDTO;
use App\Support\TimeSeries\TimeSeriesSeriesDTO;
use App\Support\TimeSeries\TimeSeriesTableMetadataDTO;
use InvalidArgumentException;

final class MetricsTimeSeriesAdapter
{
    /** @param array<int|string, mixed> $response */
    public function fromResponse(array $response): TimeSeriesChartDTO
    {
        $table = $this->table();

        if (($response['ok'] ?? true) === false) {
            return $this->error($table);
        }

        $payload = $response['data'] ?? null;
        if (is_array($payload) && isset($payload['data'])) {
            $payload = $payload['data'];
        }

        if (! is_array($payload)) {
            return $this->error($table);
        }

        try {
            $points = array_is_list($payload)
                ? $this->pointsFromRows($payload)
                : $this->pointsFromParallelArrays($payload);

            if ($points === []) {
                return TimeSeriesChartDTO::empty(
                    'metrics-time-series',
                    lang('Metrics.trends'),
                    lang('Metrics.trends_description'),
                    $table,
                    lang('Metrics.chart_empty'),
                );
            }

            return TimeSeriesChartDTO::ready(
                'metrics-time-series',
                lang('Metrics.trends'),
                lang('Metrics.trends_description'),
                [new TimeSeriesSeriesDTO('requests', lang('Metrics.requests'), lang('Metrics.requests_unit'), $points)],
                $table,
            );
        } catch (InvalidArgumentException) {
            return $this->error($table);
        }
    }

    private function table(): TimeSeriesTableMetadataDTO
    {
        return new TimeSeriesTableMetadataDTO(
            lang('Metrics.trends_table_caption'),
            lang('TableColumns.period'),
            [lang('Metrics.requests')],
        );
    }

    /**
     * @param array<int|string, mixed> $payload
     * @return list<TimeSeriesPointDTO>
     */
    private function pointsFromParallelArrays(array $payload): array
    {
        $dates = $payload['dates'] ?? null;
        $requests = $payload['requests'] ?? null;
        if (! is_array($dates) || ! is_array($requests) || count($dates) !== count($requests)) {
            throw new InvalidArgumentException('Metrics dates and requests must be aligned.');
        }

        if (count($dates) > TimeSeriesSeriesDTO::MAX_POINTS) {
            throw new InvalidArgumentException('The metrics series is too large.');
        }

        $points = [];
        $previousLabel = null;
        foreach ($dates as $index => $date) {
            $label = $this->label($date);
            if ($previousLabel !== null && strcmp($label, $previousLabel) <= 0) {
                throw new InvalidArgumentException('The metrics labels are not ordered.');
            }
            $previousLabel = $label;

            $value = $this->value($requests[$index] ?? null);
            if ($value !== null) {
                $points[] = new TimeSeriesPointDTO($label, $value);
            }
        }

        return $points;
    }

    /**
     * @param array<int|string, mixed> $rows
     * @return list<TimeSeriesPointDTO>
     */
    private function pointsFromRows(array $rows): array
    {
        if (count($rows) > TimeSeriesSeriesDTO::MAX_POINTS) {
            throw new InvalidArgumentException('The metrics series is too large.');
        }

        $points = [];
        $previousLabel = null;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException('The metrics row is invalid.');
            }

            $label = $this->label($row['period'] ?? $row['date'] ?? $row['label'] ?? $row['timestamp'] ?? $row['group_by'] ?? null);
            if ($previousLabel !== null && strcmp($label, $previousLabel) <= 0) {
                throw new InvalidArgumentException('The metrics labels are not ordered.');
            }
            $previousLabel = $label;

            $value = $this->value($row['value'] ?? $row['requests'] ?? null);
            if ($value !== null) {
                $points[] = new TimeSeriesPointDTO($label, $value);
            }
        }

        return $points;
    }

    private function label(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new InvalidArgumentException('The metrics label is invalid.');
        }

        $label = trim((string) $value);
        if ($label === '') {
            throw new InvalidArgumentException('The metrics label is empty.');
        }

        return $label;
    }

    private function value(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        if (! is_int($value) && ! is_float($value) && ! is_string($value)) {
            throw new InvalidArgumentException('The metrics value is invalid.');
        }

        $value = trim((string) $value);
        if ($value === '' || ! is_numeric($value)) {
            throw new InvalidArgumentException('The metrics value is not numeric.');
        }

        $numeric = (float) $value;
        if (! is_finite($numeric) || $numeric < 0) {
            throw new InvalidArgumentException('The metrics value is outside the accepted range.');
        }

        return $numeric;
    }

    private function error(TimeSeriesTableMetadataDTO $table): TimeSeriesChartDTO
    {
        return TimeSeriesChartDTO::error(
            'metrics-time-series',
            lang('Metrics.trends'),
            lang('Metrics.trends_description'),
            $table,
            lang('Metrics.chart_error'),
        );
    }
}
