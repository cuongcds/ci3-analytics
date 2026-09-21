<?php

namespace Open\Analytics\Services;

use Open\Analytics\Contracts\EventRepositoryInterface;
use Open\Analytics\Contracts\SubjectResolverInterface;
use Open\Analytics\Contracts\VisitorRepositoryInterface;
use Open\Analytics\Support\DailySeries;
use Open\Analytics\Support\DateRange;
use Open\Analytics\Support\Domain;

/**
 * Framework-free core: recording events and building the report data a
 * dashboard needs. The CI3 adapter's Analytics_lib is a thin wrapper
 * around this — see adapters/codeigniter3/libraries/Analytics_lib.php.
 */
class AnalyticsService
{
    public const ALLOWED_EVENT_TYPES = ['page_view', 'click', 'form_submit', 'search'];

    /** @var VisitorRepositoryInterface */
    private $visitors;

    /** @var EventRepositoryInterface */
    private $events;

    /** @var SubjectResolverInterface|null */
    private $subjectResolver;

    /** @var DateRange */
    private $dateRange;

    public function __construct(
        VisitorRepositoryInterface $visitors,
        EventRepositoryInterface $events,
        ?SubjectResolverInterface $subjectResolver = null,
        ?DateRange $dateRange = null
    ) {
        $this->visitors = $visitors;
        $this->events = $events;
        $this->subjectResolver = $subjectResolver;
        $this->dateRange = $dateRange ?: new DateRange();
    }

    public function dateRange(): DateRange
    {
        return $this->dateRange;
    }

    /**
     * Records one tracked event. $input carries: event_type (required,
     * must be one of ALLOWED_EVENT_TYPES), visitor_uid, path, domain (the
     * page's own window.location.hostname, as reported by the client — see
     * Support\Domain), referrer, label, device, browser, fingerprint.
     *
     * @throws \InvalidArgumentException if event_type is missing/invalid.
     */
    public function record(array $input): int
    {
        $eventType = $input['event_type'] ?? null;
        if (!in_array($eventType, self::ALLOWED_EVENT_TYPES, true)) {
            throw new \InvalidArgumentException('Invalid event_type: ' . var_export($eventType, true));
        }

        $visitorUid = (string) ($input['visitor_uid'] ?? '');
        $this->visitors->touch(
            $visitorUid,
            $input['fingerprint'] ?? null,
            $input['device'] ?? null,
            $input['browser'] ?? null
        );

        $path = (string) ($input['path'] ?? '');
        $subjectId = $this->subjectResolver ? $this->subjectResolver->resolve($path) : null;

        return $this->events->record([
            'visitor_uid' => $visitorUid,
            'event_type' => $eventType,
            'subject_id' => $subjectId,
            'path' => mb_substr($path, 0, 512),
            'domain' => Domain::normalize($input['domain'] ?? null),
            'referrer' => mb_substr((string) ($input['referrer'] ?? ''), 0, 512),
            'label' => mb_substr((string) ($input['label'] ?? ''), 0, 255),
            'device' => $input['device'] ?? null,
            'browser' => $input['browser'] ?? null,
        ]);
    }

    /**
     * Everything a dashboard/report page needs for one resolved date
     * range: summary counts, the daily page-view series, DAU (daily
     * distinct visitors), the event-type breakdown, and top-subjects (if a
     * SubjectResolverInterface was provided).
     *
     * event_breakdown is per-event-type totals for the range — e.g.
     * [['event_type' => 'page_view', 'total' => 120], ['event_type' =>
     * 'click', 'total' => 34], ...], ordered by total descending. Useful
     * for a simple list/pie breakdown of what kind of activity dominates.
     */
    public function buildReport(array $rangeParams, int $topSubjectsLimit = 20): array
    {
        [$startDate, $endDate] = $this->dateRange->resolve($rangeParams);
        $from = strtotime($startDate . ' 00:00:00');
        $to = strtotime($endDate . ' 23:59:59');

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unique_visitors' => $this->visitors->countUnique($from, $to),
            'page_views' => $this->events->countByType('page_view', $from, $to),
            'clicks' => $this->events->countByType('click', $from, $to),
            'daily_page_views' => DailySeries::fill($this->events->getDailySeries('page_view', $from, $to), $startDate, $endDate),
            'daily_active_visitors' => DailySeries::fill($this->events->getDailyActiveVisitors($from, $to), $startDate, $endDate),
            'event_breakdown' => $this->events->getEventTypeBreakdown($from, $to),
            'top_subjects' => $this->subjectResolver ? $this->events->getTopSubjects($from, $to, $topSubjectsLimit) : [],
            'top_paths' => $this->events->getTopPaths($from, $to, $topSubjectsLimit),
            'top_domains' => $this->events->getTopDomains($from, $to, $topSubjectsLimit),
        ];
    }
}
