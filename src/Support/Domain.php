<?php

namespace Open\Analytics\Support;

/**
 * The domain a tracked page was actually served on. The tracking script is
 * always loaded from the host app's own service (see the CI3 adapter's
 * Analytics_track controller), but the page embedding it can be on any
 * domain — the host's own, a custom domain aliased to a subject, or a
 * third-party site embedding the tracker entirely. The server therefore has
 * no reliable way to infer this from the request's own Host header (that
 * would only ever read the tracking service's own host).
 *
 * Two sources are available instead, in order of trust:
 *  1. The request's Origin header — set by the browser itself on every
 *     cross-origin fetch()/sendBeacon() call, so it can't be forged by
 *     page JS. Carries no path, only scheme+host(+port).
 *  2. The `domain` field the client reports from window.location.hostname
 *     (see adapters/codeigniter3/assets/js/open-analytics.js) — needed
 *     anyway for same-origin requests (some browsers omit Origin on those)
 *     and as a fallback wherever Origin is missing.
 * Analytics_track::track() resolves Origin first, falling back to the
 * posted field — see fromOriginHeader() / normalize().
 */
class Domain
{
    private const MAX_LENGTH = 255;

    public static function normalize(?string $value): ?string
    {
        $domain = strtolower(trim((string) $value));
        $domain = preg_replace('/:\d+$/', '', $domain);

        return $domain !== '' ? mb_substr($domain, 0, self::MAX_LENGTH) : null;
    }

    /**
     * Extracts just the host from an Origin header value (e.g.
     * "https://tool2.com" -> "tool2.com"), or null if the header is
     * missing/unparsable.
     */
    public static function fromOriginHeader(?string $origin): ?string
    {
        if (empty($origin)) {
            return null;
        }

        $host = parse_url($origin, PHP_URL_HOST);
        return self::normalize($host);
    }
}
