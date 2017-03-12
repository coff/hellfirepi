<?php

namespace Coff\Hellfire\System;

use Casadatos\Component\Dashboard\Dashboard;

trait DashboardTrait
{
    /** @var  Dashboard */
    protected $dashboard;

    /**
     * @param Dashboard $dashboard
     * @return $this
     */
    public function setDashboard(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * @return Dashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }
}
