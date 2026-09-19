<?php

namespace Open\Analytics\Contracts;

interface EventRepositoryInterface
{
    /**
     * Persist one event. $data carries: visitor_uid, event_type, subject_id
     * (nullable — e.g. a "tool" or "product" id the event is about),
     * path, referrer, label, device, browser. created_at is stamped by the
     * implementation.
     */
    public function record(array $data): int;

    public function countByType(string $eventType, ?int $from, ?int $to): int;

    /**
     * Daily totals for one event type, as [['day' => 'Y-m-d', 'total' => int], ...],
     * ordered by day ascending.
     */
    public function getDailySeries(string $eventType, int $from, int $to): array;

    /**
     * Event counts grouped by event_type, as [['event_type' => string, 'total' => int], ...],
     * ordered by total descending.
     */
    public function getEventTypeBreakdown(int $from, int $to): array;

    /**
     * Per-subject effectiveness: how many page_view vs click events each
     * subject_id got in the range. Shape is intentionally minimal —
     * [['subject_id' => int, 'page_views' => int, 'clicks' => int], ...],
     * ordered by page_views descending. The host app joins subject_id back
     * to its own table (tool, product, article...) for display.
     */
    public function getTopSubjects(int $from, int $to, int $limit = 20): array;
}
