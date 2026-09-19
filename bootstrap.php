<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * cuongcds/ci3-analytics bootstrap.
 *
 * Required from the host application's application/config/config.php:
 *
 *   require_once APPPATH . '../vendor/cuongcds/ci3-analytics/bootstrap.php';
 *
 * At this point in CI3's lifecycle only the local $config array (being
 * built by config.php) exists — there is no $CI instance yet, so this
 * file limits itself to loading the Composer autoloader and merging the
 * package's default 'analytics' config with any
 * application/config/analytics.php overrides. Package-path/library
 * registration happens lazily on first controller instantiation (see
 * adapters/codeigniter3/core/Analytics_Controller.php).
 */

if (!class_exists(\Open\Analytics\Support\DateRange::class)) {
    // When installed as a normal Composer dependency, this package lives at
    // vendor/cuongcds/ci3-analytics/ and the real autoloader is two levels up,
    // at vendor/autoload.php. Fall back to __DIR__ . '/vendor/autoload.php' for
    // the (monorepo/local-path-repository) case where this file sits at the repo
    // root and vendor/ is its own, sibling directory instead.
    $composerAutoload = __DIR__ . '/../../autoload.php';

    require_once is_file($composerAutoload) ? $composerAutoload : __DIR__ . '/vendor/autoload.php';
}

/*
 * Analytics_lib must be reachable even before any package path has been
 * registered with CI3's loader (that registration is done lazily, by
 * Analytics_Controller's constructor, once a Loader exists): if the host
 * app's own MY_Controller loads the `analytics_lib` library (e.g. to call
 * setUserProvider()) before any Analytics_Controller-derived controller has
 * run, CI3's loader would only look under application/libraries/ and
 * BASEPATH/libraries/ — the package path isn't registered yet, so it
 * wouldn't find it. Requiring it eagerly here makes CI3's loader see the
 * class already defined and skip file resolution entirely (see
 * CI_Loader::_ci_load_library()'s class_exists() check). Analytics_lib has
 * no CI3 base-class dependency, so it's safe to load this early.
 */
require_once __DIR__ . '/adapters/codeigniter3/libraries/Analytics_lib.php';

/*
 * Analytics_Controller, by contrast, extends CI_Controller (or the host's
 * MY_Controller, which itself extends CI_Controller) — neither class
 * exists yet this early in the request (CI_Controller is only defined
 * later by CI3's own CodeIgniter.php flow), so Analytics_Controller can't
 * be required eagerly here. Autoload it lazily instead, the first time an
 * adapter controller file references it in a `class X extends
 * Analytics_Controller` declaration (which only happens once CI_Controller
 * is available).
 */
spl_autoload_register(function ($class) {
    if ($class === 'Analytics_Controller') {
        require_once __DIR__ . '/adapters/codeigniter3/core/Analytics_Controller.php';
    }
});

// adapters/codeigniter3/config/analytics.php assigns the package defaults into $config['analytics'].
require __DIR__ . '/adapters/codeigniter3/config/analytics.php';
$analyticsDefaults = $config['analytics'];

$analyticsOverrides = [];
$analyticsOverridesFile = realpath(APPPATH . 'config/analytics.php');

if ($analyticsOverridesFile && is_file($analyticsOverridesFile)) {
    // application/config/analytics.php also assigns into $config['analytics'] — capture just that.
    require $analyticsOverridesFile;
    $analyticsOverrides = $config['analytics'];
}

$config['analytics'] = \Open\Analytics\Support\Config::merge($analyticsDefaults, $analyticsOverrides);

/*
 * Bootstrapping problem: CI3's core dispatch only ever looks for the requested
 * controller under application/controllers/ (see Stub_generator.php for the full
 * explanation), and `php index.php analytics install` is itself dispatched that
 * way — so the CLI controller stub (application/controllers/Analytics.php) has
 * to exist before `analytics install` can ever run to generate the rest.
 * Self-heal it here on every request instead of requiring a manual one-time
 * copy step.
 */
$analyticsCliStub = APPPATH . 'controllers/Analytics.php';

if (!is_file($analyticsCliStub)) {
    require_once __DIR__ . '/adapters/codeigniter3/core/Stub_generator.php';

    if (!is_dir(APPPATH . 'controllers')) {
        mkdir(APPPATH . 'controllers', 0755, true);
    }

    file_put_contents(
        $analyticsCliStub,
        \Stub_generator::stub_contents_for(
            __DIR__ . '/adapters/codeigniter3/controllers/Analytics.php',
            APPPATH . 'controllers'
        )
    );
}
