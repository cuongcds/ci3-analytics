<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends Analytics_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->analytics_lib->userProvider()->canViewDashboard()) {
            show_error('You do not have permission to view analytics.', 403);
        }
    }

    /**
     * Renders the package's own standalone dashboard page. Most hosts will
     * instead embed the _overview/_summary_cards partials (see the views/
     * directory) into their own admin layout — this action exists so the
     * package is still fully usable stand-alone, right after `analytics install`.
     */
    public function index()
    {
        $report = $this->analytics_lib->buildReport($this->input->get());

        $this->load->view('admin/pages/analytics/index', [
            'config' => $this->analytics_lib->config(),
            'report' => $report,
            'dateRange' => $this->analytics_lib->service()->dateRange(),
            'formAction' => base_url($this->analytics_lib->config('admin_prefix')),
        ]);
    }
}
