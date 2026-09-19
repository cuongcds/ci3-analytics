<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Open\Analytics\Adapters\CodeIgniter3\EventRepositoryCi3;
use Open\Analytics\Adapters\CodeIgniter3\VisitorRepositoryCi3;
use Open\Analytics\Contracts\AnalyticsUserProviderInterface;
use Open\Analytics\Contracts\SubjectResolverInterface;
use Open\Analytics\Services\AnalyticsService;
use Open\Analytics\Support\DateRange;
use Open\Analytics\Support\UserAgent;
use Open\Analytics\Support\VisitorId;

/**
 * DI bridge between CI3 and the framework-free Core. Loaded as
 * $this->load->library('analytics_lib'), exposed as $this->analytics_lib
 * in controllers.
 *
 * Named Analytics_lib (not Analytics) to avoid a class-name collision with
 * the CLI controller adapters/codeigniter3/controllers/Analytics.php,
 * which CI3's routing convention forces to be named exactly `Analytics`.
 */
class Analytics_lib
{
    /** @var array */
    protected $config;

    /** @var AnalyticsService|null */
    protected $service;

    /** @var SubjectResolverInterface|null */
    protected $subjectResolver;

    /** @var AnalyticsUserProviderInterface|null */
    protected $userProvider;

    public function __construct()
    {
        $CI = &get_instance();
        // $config['analytics'] is already merged (package defaults +
        // application/config/analytics.php overrides) by bootstrap.php.
        $this->config = $CI->config->item('analytics') ?: [];
    }

    public function config($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function setSubjectResolver(SubjectResolverInterface $resolver): void
    {
        $this->subjectResolver = $resolver;
        $this->service = null; // rebuild with the resolver on next access
    }

    public function setUserProvider(AnalyticsUserProviderInterface $provider): void
    {
        $this->userProvider = $provider;
    }

    public function userProvider(): AnalyticsUserProviderInterface
    {
        if ($this->userProvider === null) {
            throw new \RuntimeException(
                'No AnalyticsUserProviderInterface has been registered. Call $this->analytics_lib->setUserProvider(...) first.'
            );
        }

        return $this->userProvider;
    }

    public function service(): AnalyticsService
    {
        if ($this->service === null) {
            $this->service = $this->buildService();
        }
        return $this->service;
    }

    /**
     * Records one incoming track request. $request is the raw associative
     * array of POSTed fields (event_type, path, referrer, label) plus the
     * User-Agent string and any existing visitor cookie value.
     *
     * Returns the resolved visitor_uid so the caller (the Track controller)
     * can set/refresh the cookie.
     */
    public function trackFromRequest(array $request, ?string $existingVisitorUid, ?string $userAgent, ?string $ipAddress): string
    {
        $visitorUid = VisitorId::isValid($existingVisitorUid) ? $existingVisitorUid : VisitorId::generate();
        [$device, $browser] = UserAgent::parse($userAgent);
        $fingerprint = $ipAddress !== null ? hash('sha256', $ipAddress . '|' . $userAgent) : null;

        $this->service()->record([
            'event_type' => $request['event_type'] ?? null,
            'visitor_uid' => $visitorUid,
            'path' => $request['path'] ?? '',
            'referrer' => $request['referrer'] ?? '',
            'label' => $request['label'] ?? '',
            'device' => $device,
            'browser' => $browser,
            'fingerprint' => $fingerprint,
        ]);

        return $visitorUid;
    }

    public function buildReport(array $rangeParams, int $topSubjectsLimit = 20): array
    {
        return $this->service()->buildReport($rangeParams, $topSubjectsLimit);
    }

    protected function buildService(): AnalyticsService
    {
        $CI = &get_instance();
        $CI->load->model('analytics_visitor_model');
        $CI->load->model('analytics_event_model');

        $dateRangeConfig = $this->config('date_range', []);
        $dateRange = new DateRange(
            $dateRangeConfig['presets'] ?? [7, 28, 90],
            $dateRangeConfig['max_range_days'] ?? 90,
            $dateRangeConfig['max_lookback_days'] ?? 120
        );

        // A resolver set via setSubjectResolver() wins; otherwise fall back
        // to instantiating the class named in config('subject_resolver').
        $resolver = $this->subjectResolver;
        if ($resolver === null) {
            $resolverClass = $this->config('subject_resolver');
            $resolver = $resolverClass ? new $resolverClass() : null;
        }

        return new AnalyticsService(
            new VisitorRepositoryCi3($CI->analytics_visitor_model),
            new EventRepositoryCi3($CI->analytics_event_model),
            $resolver,
            $dateRange
        );
    }
}
