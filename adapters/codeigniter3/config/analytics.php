<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Package defaults for $config['analytics']. Copied to
 * application/config/analytics.php by `php index.php analytics install`
 * (or on first bootstrap if that file doesn't exist yet) — override any
 * key there, this file itself is never read after that.
 */
$config['analytics'] = [
    'enabled' => true,

    // Public tracking endpoint, e.g. base_url('analytics/track').
    'track_route' => 'analytics/track',

    // Admin dashboard route prefix, e.g. base_url('admin/analytics').
    'admin_prefix' => 'admin/analytics',

    'tables' => [
        'visitors' => 'analytics_visitors',
        'events' => 'analytics_events',
        'migrations' => 'analytics_migrations',
    ],

    'visitor_cookie' => [
        'name' => 'oa_uid',
        'lifetime_days' => 365,
    ],

    /*
     * Origins allowed to POST to the track endpoint from a different
     * domain (e.g. https://my-site.com), so the visitor cookie is sent
     * and readable across a shared/centralized analytics deployment.
     * Same-origin requests always work regardless of this setting; leave
     * empty ([]) if every site tracks its own events same-origin.
     * Wildcards are not supported on purpose — Access-Control-Allow-Origin
     * can't be "*" together with Access-Control-Allow-Credentials: true,
     * so each allowed origin must be listed explicitly.
     */
    'cors' => [
        'allowed_origins' => [
            // 'https://my-site.com',
        ],
    ],

    // Dashboard date-range filter constraints — see Open\Analytics\Support\DateRange.
    'date_range' => [
        'presets' => [7, 28, 90],
        'max_range_days' => 90,
        'max_lookback_days' => 120,
    ],

    // Set to a class implementing Open\Analytics\Contracts\SubjectResolverInterface
    // (or leave null) to enable the "top subjects" per-item effectiveness report.
    'subject_resolver' => null,
];
