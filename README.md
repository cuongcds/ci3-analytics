# cuongcds/ci3-analytics

[![Packagist](https://img.shields.io/packagist/v/cuongcds/ci3-analytics)](https://packagist.org/packages/cuongcds/ci3-analytics)

Reusable first-party analytics (page views, clicks, search, and per-item
effectiveness) Composer package for CodeIgniter 3.1.13 projects. Pairs
with [`open-analytics`](https://github.com/cuongcds/open-analytics) for
the client-side tracker script, but the track endpoint accepts events
from any client.

## Installation

```bash
composer require cuongcds/ci3-analytics
```

Add the bootstrap require to `application/config/config.php`, right after the `$config['composer_autoload']` block:

```php
require_once APPPATH . '../vendor/cuongcds/ci3-analytics/bootstrap.php';
```

Then install:

```bash
php index.php analytics install
```

This creates `application/config/analytics.php`, runs migrations, generates
`application/config/analytics_routes.php` with a single
`require_once __DIR__ . '/analytics_routes.php';` line added to
`application/config/routes.php`, and publishes the controller stubs
`analytics install` needs (see "Architecture" below).

- Track endpoint: `/analytics/track` (POST, matches `open-analytics`'s default)
- Admin dashboard: `/admin/analytics` (a small standalone page — most hosts
  will embed the partials into their own admin layout instead, see below)

## Deploying to a new server

```bash
composer install --no-dev
php index.php analytics migrate
php index.php analytics publish
```

## Gating the dashboard

The package has no opinion on your auth system. Implement
`AnalyticsUserProviderInterface` and register it before the dashboard
controller runs — typically in your own `MY_Controller`:

```php
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('analytics_lib');
        $this->analytics_lib->setUserProvider(new My_analytics_user_provider());
    }
}
```

```php
use Open\Analytics\Contracts\AnalyticsUserProviderInterface;

class My_analytics_user_provider implements AnalyticsUserProviderInterface
{
    public function canViewDashboard(): bool
    {
        $CI = &get_instance();
        return ($CI->session->userdata('member')['user_kind'] ?? null) === 'admin';
    }
}
```

## Client-side tracking

Load [`open-analytics`](https://github.com/cuongcds/open-analytics) from
jsDelivr — no separate install needed:

```html
<script src="https://cdn.jsdelivr.net/npm/@cuongcds/open-analytics@0.1.0/dist/open-analytics.min.js"
	data-endpoint="/analytics/track"></script>
```

Or `npm install @cuongcds/open-analytics` and self-host `dist/open-analytics.min.js` instead.

Mark any element you want click-tracked with `data-track="some_label"`.

### Accepting events from another domain

If a different site posts to this endpoint (a shared/centralized analytics
deployment, `open-analytics`'s `data-endpoint` pointed at a full URL),
list its origin in `application/config/analytics.php`:

```php
$config['analytics']['cors']['allowed_origins'] = [
    'https://my-other-site.com',
];
```

`Analytics_track` then echoes back that exact origin (never a wildcard —
required for `Access-Control-Allow-Credentials: true` to be valid) with
credentials allowed, so the visitor cookie survives the cross-origin
request. Same-origin requests work regardless of this setting.

## Per-item effectiveness ("top subjects")

To report page views/clicks per tool/product/article, implement
`SubjectResolverInterface` against your own URL scheme:

```php
use Open\Analytics\Contracts\SubjectResolverInterface;

class Tool_subject_resolver implements SubjectResolverInterface
{
    public function resolve(string $path): ?int
    {
        // e.g. /tools/{category}/{slug}/ -> tools.id
        $segments = trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
        $segments = explode('/', $segments);
        if (count($segments) < 3 || $segments[0] !== 'tools') {
            return null;
        }
        $CI = &get_instance();
        $CI->load->model('tool_model');
        $tool = $CI->tool_model->getByCategoryAndSlug($segments[1], $segments[2]);
        return $tool['id'] ?? null;
    }
}
```

Register it once, before events are tracked (e.g. in `MY_Controller` alongside `setUserProvider()`):

```php
$this->analytics_lib->setSubjectResolver(new Tool_subject_resolver());
```

`getTopSubjects()` then returns `[['subject_id' => int, 'page_views' => int, 'clicks' => int], ...]` —
join `subject_id` back to your own table for names/links (see the "Top subjects" block in
`adapters/codeigniter3/views/admin/pages/analytics/index.php` for the join pattern).

## Embedding into your own dashboard

Rather than linking to the package's standalone `/admin/analytics` page, most
hosts will want the summary cards and chart inline in their own dashboard. Both
pieces are separate, embeddable view partials:

```php
// In your own controller:
$this->load->library('analytics_lib');
$report = $this->analytics_lib->buildReport($this->input->get());

$this->load->view('your/dashboard', [
    'report' => $report,
    'dateRange' => $this->analytics_lib->service()->dateRange(),
    'formAction' => base_url('admin/dashboard'),
]);
```

```php
// In your/dashboard.php view:
$this->load->view('admin/pages/analytics/_summary_cards', ['report' => $report]);
$this->load->view('admin/pages/analytics/_overview', compact('report', 'formAction', 'dateRange'));
```

The bundled `_summary_cards`/`_overview`/`stat_card` views use minimal inline
styles so the package works standalone. If your app already has a themed
stat-card component (Tailwind, Bootstrap, etc.), copy the two lines in
`_summary_cards.php` and call your own component instead — see
`open-tools`'s own `admin/components/stat_card.php` for the pattern this was
extracted from.

## Configuration

Override any key in `application/config/analytics.php` (created by
`analytics install`) — table names, `track_route`/`admin_prefix`, the
visitor cookie name/lifetime, and the date-range filter's presets/limits.

## Architecture

- `src/` — framework-free Core (`Open\Analytics\` namespace, Composer PSR-4):
  `Contracts/` (repository + resolver + user-provider interfaces),
  `Services/AnalyticsService` (record + report building), `Support/`
  (`DateRange`, `UserAgent`, `VisitorId`, `Config`), `Adapters/CodeIgniter3/`
  (thin wrappers turning the CI3 models into the Contracts interfaces).
- `adapters/codeigniter3/` — CI3 adapter: controllers (public track
  endpoint, CLI install/migrate, admin dashboard), `CI_Model` subclasses,
  the `Analytics_lib` library (DI bridge, exposed as `$this->analytics_lib`),
  views, migrations, default config.

CI3's dispatcher only ever looks for the requested controller under
`application/controllers/` — it never searches package paths for the
*initial* controller. `php index.php analytics install` (and `analytics
publish`) therefore generates small stub files under
`application/controllers/` that `require` the real package controllers; do
not edit these stubs, they're regenerated on every install/publish.

Routes work the same way: all package routes live in a generated
`application/config/analytics_routes.php` (regenerated on every install),
and `application/config/routes.php` itself is only ever touched once, to
add a single `require_once __DIR__ . '/analytics_routes.php';` line — no
marker-based text surgery on a file you may also be editing by hand.

## Releasing

1. Bump `version` isn't tracked in `composer.json` — Packagist reads
   versions from git tags instead.
2. `git tag vX.Y.Z && git push --tags`
3. On [Packagist](https://packagist.org/packages/cuongcds/ci3-analytics),
   the GitHub Service Hook (configured once, under the package's Settings)
   auto-updates on every push/tag; trigger a manual "Update" there if it
   hasn't picked up the new tag within a minute or two.

## Out of scope (this version)

Bot/crawler filtering, IP geolocation, funnels/retention/cohort reports,
multi-tenant (per-website) tracking, data export, GDPR consent management —
the visitor cookie is a plain anonymous id with no PII attached.
