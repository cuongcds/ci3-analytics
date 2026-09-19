<?php

namespace Open\Analytics\Adapters\CodeIgniter3;

use Open\Analytics\Contracts\EventRepositoryInterface;

/**
 * Wraps the CI3 Analytics_event_model (a plain CI_Model instance, already
 * loaded by the host app's Loader) behind the framework-free
 * EventRepositoryInterface.
 */
class EventRepositoryCi3 implements EventRepositoryInterface
{
    /** @var \Analytics_event_model */
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function record(array $data): int
    {
        return (int) $this->model->record($data);
    }

    public function countByType(string $eventType, ?int $from, ?int $to): int
    {
        return (int) $this->model->countByType($eventType, $from, $to);
    }

    public function getDailySeries(string $eventType, int $from, int $to): array
    {
        return $this->model->getDailySeries($eventType, $from, $to);
    }

    public function getEventTypeBreakdown(int $from, int $to): array
    {
        return $this->model->getEventTypeBreakdown($from, $to);
    }

    public function getTopSubjects(int $from, int $to, int $limit = 20): array
    {
        return $this->model->getTopSubjects($from, $to, $limit);
    }
}
