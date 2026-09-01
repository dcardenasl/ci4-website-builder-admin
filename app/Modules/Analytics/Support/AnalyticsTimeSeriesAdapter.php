<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Support;

use App\Support\TimeSeries\TimeSeriesChartDTO;
use App\Support\TimeSeries\TimeSeriesPointDTO;
use App\Support\TimeSeries\TimeSeriesSeriesDTO;
use App\Support\TimeSeries\TimeSeriesTableMetadataDTO;
use InvalidArgumentException;

final class AnalyticsTimeSeriesAdapter
{
    /** @param array<int|string, mixed> $response */
    public function fromResponse(array $response): TimeSeriesChartDTO
    {
        $table = $this->table();

        if (($response['ok'] ?? true) === false) {
            return TimeSeriesChartDTO::error(
                'analytics-time-series',
                lang('Analytics.trend_title'),
                lang('Analytics.trend_description'),
                $table,
                lang('Analytics.chart_error'),
            );
        }

        $rows = $response['data'] ?? null;
        // ApiClient stores the complete JSON envelope under `data`, while
        // this endpoint returns its series under a second `data` key. Accept
        // both the direct service shape and the live ApiClient shape without
        // weakening validation for malformed associative payloads.
        for ($depth = 0; $depth < 2; $depth++) {
            if (! is_array($rows) || ! array_key_exists('data', $rows)) {
                break;
            }

            $rows = $rows['data'];
        }

        if (! is_array($rows) || ! array_is_list($rows)) {
            return TimeSeriesChartDTO::error(
                'analytics-time-series',
                lang('Analytics.trend_title'),
                lang('Analytics.trend_description'),
                $table,
                lang('Analytics.chart_error'),
            );
        }

        if ($rows === []) {
            return TimeSeriesChartDTO::empty(
                'analytics-time-series',
                lang('Analytics.trend_title'),
                lang('Analytics.trend_description'),
                $table,
                lang('Analytics.chart_empty'),
            );
        }

        try {
            if (count($rows) > TimeSeriesSeriesDTO::MAX_POINTS) {
                throw new InvalidArgumentException('The analytics series is too large.');
            }

            $views = [];
            $visitors = [];
            $previousLabel = null;
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    throw new InvalidArgumentException('The analytics row is invalid.');
                }

                $label = $this->label($row);
                if ($previousLabel !== null && strcmp($label, $previousLabel) <= 0) {
                    throw new InvalidArgumentException('The analytics labels are not ordered.');
                }
                $previousLabel = $label;

                $viewValue = $this->value($row['views'] ?? null);
                $visitorValue = $this->value($row['unique_visitors'] ?? null);
                if ($viewValue === null || $visitorValue === null) {
                    continue;
                }

                $views[] = new TimeSeriesPointDTO($label, $viewValue);
                $visitors[] = new TimeSeriesPointDTO($label, $visitorValue);
            }

            if ($views === []) {
                return TimeSeriesChartDTO::empty(
                    'analytics-time-series',
                    lang('Analytics.trend_title'),
                    lang('Analytics.trend_description'),
                    $table,
                    lang('Analytics.chart_empty'),
                );
            }

            return TimeSeriesChartDTO::ready(
                'analytics-time-series',
                lang('Analytics.trend_title'),
                lang('Analytics.trend_description'),
                [
                    new TimeSeriesSeriesDTO('views', lang('Analytics.col_views'), lang('Analytics.views_unit'), $views),
                    new TimeSeriesSeriesDTO('unique_visitors', lang('Analytics.col_visitors'), lang('Analytics.visitors_unit'), $visitors),
                ],
                $table,
            );
        } catch (InvalidArgumentException) {
            return TimeSeriesChartDTO::error(
                'analytics-time-series',
                lang('Analytics.trend_title'),
                lang('Analytics.trend_description'),
                $table,
                lang('Analytics.chart_error'),
            );
        }
    }

    private function table(): TimeSeriesTableMetadataDTO
    {
        return new TimeSeriesTableMetadataDTO(
            lang('Analytics.trend_table_caption'),
            lang('TableColumns.period'),
            [lang('Analytics.col_views'), lang('Analytics.col_visitors')],
        );
    }

    /** @param array<string, mixed> $row */
    private function label(array $row): string
    {
        $label = $row['label'] ?? $row['period'] ?? $row['date'] ?? null;
        if (! is_string($label) && ! is_int($label) && ! is_float($label)) {
            throw new InvalidArgumentException('The analytics label is invalid.');
        }

        $label = trim((string) $label);
        if ($label === '') {
            throw new InvalidArgumentException('The analytics label is empty.');
        }

        return $label;
    }

    private function value(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        if (! is_int($value) && ! is_float($value) && ! is_string($value)) {
            throw new InvalidArgumentException('The analytics value is invalid.');
        }

        $value = trim((string) $value);
        if ($value === '' || ! is_numeric($value)) {
            throw new InvalidArgumentException('The analytics value is not numeric.');
        }

        $numeric = (float) $value;
        if (! is_finite($numeric) || $numeric < 0) {
            throw new InvalidArgumentException('The analytics value is outside the accepted range.');
        }

        return $numeric;
    }
}
