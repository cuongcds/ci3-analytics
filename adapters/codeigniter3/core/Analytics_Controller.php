<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared setup for every CI3 adapter controller shipped by the package:
 * registers the package's own package-path (views/config/models) with
 * CI3's loader so nothing needs to be copied into application/, and loads
 * the `analytics_lib` library bridge.
 */
trait Analytics_Controller_Behaviour
{
    protected static $package_path_registered = false;

    public function __construct()
    {
        parent::__construct();

        $this->register_package_path();

        $this->load->library('analytics_lib');

        // Package views use base_url() unconditionally; don't rely on the
        // host app's own autoload['helper'] config to have loaded it.
        $this->load->helper('url');
    }

    protected function register_package_path()
    {
        if (self::$package_path_registered) {
            return;
        }

        $this->load->add_package_path(realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

        self::$package_path_registered = true;
    }
}

/*
 * If the host application defines its own MY_Controller (application/core/MY_Controller.php),
 * extend that instead of CI_Controller directly, so that any setup it does
 * there — most importantly registering an AnalyticsUserProviderInterface
 * via $this->analytics_lib->setUserProvider() — also runs before the
 * dashboard controller executes.
 */
if (!class_exists('MY_Controller', false) && file_exists(APPPATH . 'core/MY_Controller.php')) {
    require_once APPPATH . 'core/MY_Controller.php';
}

if (class_exists('MY_Controller', false)) {
    class Analytics_Controller extends MY_Controller
    {
        use Analytics_Controller_Behaviour;
    }
} else {
    class Analytics_Controller extends CI_Controller
    {
        use Analytics_Controller_Behaviour;
    }
}
