<?php

namespace Open\Analytics\Contracts;

interface EventRepositoryInterface
{
    /**
     * Persist one event. $data carries: visitor_uid, event_type, subject_id
     * (nullable — e.g. a "tool" or "product" id the event is about),
     * path, domain (nullable — the host/page origin that served the
     * tracked page, as reported by the client; see
     * Support\Domain::normalize()), referrer, label, device, browser.
     * created_at is stamped by the implementation.
     */
    public function record(array $data): int;

    public function countByType(string $eventType, ?int $from, ?int $to): int;

    /**
     * Daily totals for one event type, as [['day' => 'Y-m-d', 'total' => int], ...],
     * ordered by day ascending.
     */
    public function getDailySeries(string $eventType, int $from, int $to): array;

    /**
     * DAU: distinct visitor_uid per day (based on page_view events, one
     * per pageload), as [['day' => 'Y-m-d', 'total' => int], ...], ordered
     * by day ascending. Not the same as VisitorRepositoryInterface::countUnique(),
     * which only tracks first/last-seen — not which specific days a
     * visitor was active on.
     */
    public function getDailyActiveVisitors(int $from, int $to): array;

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

    /**
     * Most-viewed pages by path, regardless of whether the path resolves to
     * a subject — unlike getTopSubjects(), which only covers subject pages.
     * Broken down per domain, since the same path can be served on
     * multiple domains (the host app's own domain, a custom domain the
     * host aliases to a subject, or a third-party site embedding the
     * tracker entirely). Shape: [['path' => string, 'domain' => ?string,
     * 'page_views' => int], ...], ordered by page_views descending.
     */
    public function getTopPaths(int $from, int $to, int $limit = 20): array;

    /**
     * Which domains get the most traffic. Shape: [['domain' => string,
     * 'page_views' => int, 'unique_visitors' => int], ...], ordered by
     * page_views descending. Rows with a null domain (events recorded
     * before this field existed) are excluded.
     */
    public function getTopDomains(int $from, int $to, int $limit = 20): array;
}
