<?php

namespace App\Core\Dashboard\Contracts;

use App\Models\User;

interface DashboardRepository
{
    /**
     * @return array<string, int>
     */
    public function getCounts(?User $authUser): array;

    /**
     * @return array<string, bool>
     */
    public function getVisibility(?User $authUser): array;
}
