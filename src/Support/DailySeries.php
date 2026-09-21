<?php

namespace Open\Analytics\Support;

use DateTime;

/**
 * Fills the gaps a GROUP BY day query leaves behind: a day with zero
 * events simply doesn't appear in the result set, but a chart still needs
 * a point for it (0), or the x-axis silently skips days and misleads
 * whoever's reading it.
 */
class DailySeries
{
    /**
     * @param array $rows [['day' => 'Y-m-d', 'total' => int], ...], as returned
     *   by EventRepositoryInterface::getDailySeries() — need not be sorted
     *   or contiguous.
     * @param string $startDate 'Y-m-d'
     * @param string $endDate 'Y-m-d'
     * @return array [['day' => 'Y-m-d', 'total' => int], ...] one entry per
     *   day in [startDate, endDate] inclusive, in order, missing days at 0.
     */
    public static function fill(array $rows, string $startDate, string $endDate): array
    {
        $totalsByDay = [];
        foreach ($rows as $row) {
            $totalsByDay[$row['day']] = (int) $row['total'];
        }

        $filled = [];
        $cursor = new DateTime($startDate);
        $end = new DateTime($endDate);
        while ($cursor <= $end) {
            $day = $cursor->format('Y-m-d');
            $filled[] = ['day' => $day, 'total' => $totalsByDay[$day] ?? 0];
            $cursor->modify('+1 day');
        }

        return $filled;
    }
}
