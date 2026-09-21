<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public tracking endpoint. Named Analytics_track (not Analytics) so it
 * doesn't collide with the CLI controller (adapters/codeigniter3/controllers/Analytics.php),
 * which CI3's routing convention forces to be named exactly `Analytics`.
 * Routed to $config['analytics']['track_route'] by `analytics install`
 * (see Cms.php-equivalent CLI controller's install_routes()).
 */
class Analytics_track extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->add_package_path(realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
        $this->load->library('analytics_lib');

        $this->applyCorsHeaders();
    }

    public function track()
    {
        $method = $this->input->server('REQUEST_METHOD');

        // The client's open-analytics.js only sends a simple POST (no
        // custom headers), so browsers never actually preflight this
        // endpoint — but respond correctly regardless, for any client
        // that does send an OPTIONS request.
        if ($method === 'OPTIONS') {
            $this->output->set_status_header(204)->_display();
            exit;
        }

        if ($method !== 'POST') {
            $this->fail('Invalid request', 400);
        }

        $eventType = $this->input->post('event_type');
        if (!in_array($eventType, \Open\Analytics\Services\AnalyticsService::ALLOWED_EVENT_TYPES, true)) {
            $this->fail('Invalid event type', 400);
        }

        $cookieConfig = $this->analytics_lib->config('visitor_cookie', ['name' => 'oa_uid', 'lifetime_days' => 365]);
        $existingUid = $this->input->cookie($cookieConfig['name']);

        $visitorUid = $this->analytics_lib->trackFromRequest(
            [
                'event_type' => $eventType,
                'path' => $this->input->post('path'),
                'domain' => $this->resolveDomain(),
                'referrer' => $this->input->post('referrer'),
                'label' => $this->input->post('label'),
            ],
            $existingUid,
            $this->input->server('HTTP_USER_AGENT'),
            $this->input->ip_address()
        );

        if ($visitorUid !== $existingUid) {
            $this->input->set_cookie([
                'name' => $cookieConfig['name'],
                'value' => $visitorUid,
                'expire' => $cookieConfig['lifetime_days'] * 86400,
                'httponly' => true,
            ]);
        }

        $this->respond(['success' => true]);
    }

    /**
     * The Origin header is set by the browser itself on every cross-origin
     * request and can't be forged by page JS — a more trustworthy domain
     * source than the posted `domain` field, but it has no path and some
     * browsers omit it on a same-origin request. Prefer it when present,
     * falling back to the client-reported field otherwise. See
     * Open\Analytics\Support\Domain.
     */
    protected function resolveDomain()
    {
        $fromOrigin = \Open\Analytics\Support\Domain::fromOriginHeader($this->input->server('HTTP_ORIGIN'));
        return $fromOrigin ?? $this->input->post('domain');
    }

    /**
     * 'open' mode (see adapters/codeigniter3/config/analytics.php): accepts
     * a POST from any origin, without credentials — for a tracking script
     * embedded on domains not known in advance (see Support\Domain). No
     * visitor cookie is sent/stored cross-origin in this mode; each
     * cross-origin visit gets a fresh visitor_uid.
     *
     * 'allowlist' mode (default): echoes back the requesting Origin (never
     * "*") only when it's in the configured allow-list, with
     * Allow-Credentials so the visitor cookie actually gets sent/stored
     * cross-origin for that known, fixed set of domains.
     *
     * Either way, a same-origin request (no Origin header, or one matching
     * this host) needs no CORS headers — nothing to grant there.
     */
    protected function applyCorsHeaders()
    {
        $origin = $this->input->server('HTTP_ORIGIN');
        if (empty($origin)) {
            return;
        }

        if ($this->analytics_lib->config('cors.mode', 'allowlist') === 'open') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            return;
        }

        $allowedOrigins = $this->analytics_lib->config('cors.allowed_origins', []);
        if (!in_array($origin, $allowedOrigins, true)) {
            return;
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Vary: Origin');
    }

    protected function fail($message, $statusCode)
    {
        $this->output->set_status_header($statusCode);
        $this->respond(['success' => false, 'message' => $message]);
    }

    protected function respond(array $payload)
    {
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
        exit;
    }
}
