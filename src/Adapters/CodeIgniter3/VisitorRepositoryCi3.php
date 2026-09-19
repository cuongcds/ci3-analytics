<?php

namespace Open\Analytics\Adapters\CodeIgniter3;

use Open\Analytics\Contracts\VisitorRepositoryInterface;

/**
 * Wraps the CI3 Analytics_visitor_model (a plain CI_Model instance,
 * already loaded by the host app's Loader) behind the framework-free
 * VisitorRepositoryInterface.
 */
class VisitorRepositoryCi3 implements VisitorRepositoryInterface
{
    /** @var \Analytics_visitor_model */
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function touch(string $visitorUid, ?string $fingerprint, ?string $device, ?string $browser): int
    {
        return (int) $this->model->touch($visitorUid, $fingerprint, $device, $browser);
    }

    public function countUnique(?int $from, ?int $to): int
    {
        return (int) $this->model->countUnique($from, $to);
    }
}
