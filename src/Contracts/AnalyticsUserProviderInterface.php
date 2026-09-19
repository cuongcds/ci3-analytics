<?php

namespace Open\Analytics\Contracts;

/**
 * Gates access to the admin dashboard. The package has no opinion on the
 * host app's auth system (session-based admin, JWT, RBAC...) — implement
 * this against whatever it already uses and register it via
 * $this->analytics_lib->setUserProvider($provider) before the dashboard
 * controller runs (e.g. in the host's own MY_Controller/Admin_Controller).
 */
interface AnalyticsUserProviderInterface
{
    /**
     * Whether the current request may view the analytics dashboard.
     */
    public function canViewDashboard(): bool;
}
