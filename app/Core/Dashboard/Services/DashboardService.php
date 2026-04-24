<?php

namespace App\Core\Dashboard\Services;

use App\Core\Dashboard\Contracts\DashboardRepository;
use App\Models\User;

class DashboardService
{
    public function __construct(
        private DashboardRepository $dashboards
    ) {}

    /**
     * @return array<string, int>
     */
    public function getCounts(?User $authUser): array
    {
        return $this->dashboards->getCounts($authUser);
    }

    /**
     * @return array<string, bool>
     */
    public function getVisibility(?User $authUser): array
    {
        return $this->dashboards->getVisibility($authUser);
    }
}
