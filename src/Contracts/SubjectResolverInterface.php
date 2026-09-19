<?php

namespace Open\Analytics\Contracts;

/**
 * Resolves the "subject" (tool, product, article...) an incoming event's
 * path belongs to, so per-subject effectiveness reports (getTopSubjects())
 * can group by it. Entirely host-app-specific — implement it against
 * whatever URL scheme the app uses (e.g. /tools/{category}/{slug}/).
 */
interface SubjectResolverInterface
{
    /**
     * Returns the subject id for this path, or null if the path doesn't
     * belong to any trackable subject (e.g. the homepage).
     */
    public function resolve(string $path): ?int;
}
