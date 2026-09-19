<?php

namespace Open\Analytics\Support;

use DateTime;

/**
 * Resolves a start/end date pair from raw request params (preset or
 * custom start/end), clamped so a dashboard filter form can't be used to
 * run an unbounded table scan. Framework-free: pass in a plain array
 * (e.g. $this->input->get() in a CI3 controller), get plain strings back.
 */
class DateRange
{
    /** @var int[] */
    private $allowedPresets;

    /** @var int */
    private $maxRangeDays;

    /** @var int */
    private $maxLookbackDays;

    public function __construct(array $allowedPresets = [7, 28, 90], int $maxRangeDays = 90, int $maxLookbackDays = 120)
    {
        $this->allowedPresets = $allowedPresets;
        $this->maxRangeDays = $maxRangeDays;
        $this->maxLookbackDays = $maxLookbackDays;
    }

    /**
     * @param array $params Expects optional 'preset' (int) and/or 'start'/'end' ('Y-m-d').
     * @return array{0: string, 1: string} [startDate, endDate] as 'Y-m-d'.
     */
    public function resolve(array $params): array
    {
        $today = new DateTime('today');
        $earliestStart = (clone $today)->modify('-' . $this->maxLookbackDays . ' days');

        $preset = (int) ($params['preset'] ?? 0);
        if (in_array($preset, $this->allowedPresets, true)) {
            $start = (clone $today)->modify('-' . ($preset - 1) . ' days');
            return [$start->format('Y-m-d'), $today->format('Y-m-d')];
        }

        $start = $this->parseDate($params['start'] ?? null) ?: (clone $today)->modify('-6 days');
        $end = $this->parseDate($params['end'] ?? null) ?: clone $today;

        if ($start < $earliestStart) {
            $start = clone $earliestStart;
        }
        if ($end > $today) {
            $end = clone $today;
        }
        if ($start > $end) {
            $start = clone $end;
        }

        $maxEnd = (clone $start)->modify('+' . ($this->maxRangeDays - 1) . ' days');
        if ($end > $maxEnd) {
            $end = $maxEnd;
        }

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    public function earliestStart(): string
    {
        return (new DateTime('today'))->modify('-' . $this->maxLookbackDays . ' days')->format('Y-m-d');
    }

    public function today(): string
    {
        return (new DateTime('today'))->format('Y-m-d');
    }

    /** @return array<int, string> preset days => start date ('Y-m-d'), all ending today */
    public function presetStarts(): array
    {
        $today = new DateTime('today');
        $starts = [];
        foreach ($this->allowedPresets as $days) {
            $starts[$days] = (clone $today)->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
        }
        return $starts;
    }

    private function parseDate(?string $value): ?DateTime
    {
        if (empty($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date ?: null;
    }
}
