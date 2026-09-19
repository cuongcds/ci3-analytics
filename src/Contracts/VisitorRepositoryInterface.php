<?php

namespace Open\Analytics\Contracts;

interface VisitorRepositoryInterface
{
    /**
     * Upsert a visitor by uid: increments visit_count and refreshes
     * last_seen_at/fingerprint/device/browser when it already exists,
     * inserts a fresh row otherwise. Returns the visitor's row id.
     */
    public function touch(string $visitorUid, ?string $fingerprint, ?string $device, ?string $browser): int;

    /**
     * Count distinct visitors last seen within [from, to] (unix timestamps,
     * inclusive). Null bounds mean unbounded on that side.
     */
    public function countUnique(?int $from, ?int $to): int;
}
