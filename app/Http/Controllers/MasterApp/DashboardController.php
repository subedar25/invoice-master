<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\Dashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use App\Models\Timesheet;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $user = auth()->user();

        $currentShift = array(); //Timesheet::currentShiftForUser($user->id);
        $dashboardCounts = $dashboardService->getCounts($user);
        $dashboardVisibility = $dashboardService->getVisibility($user);

        return view('masterapp.dashboard', compact('currentShift', 'dashboardCounts', 'dashboardVisibility'));
    }
    public function dashboard(DashboardService $dashboardService)
    {
        $user = auth()->user();

        $currentShift = Timesheet::currentShiftForUser($user->id);
        $dashboardCounts = $dashboardService->getCounts($user);
        $dashboardVisibility = $dashboardService->getVisibility($user);

        return view('masterapp.dashboard', compact('currentShift', 'dashboardCounts', 'dashboardVisibility'));
    }
}
