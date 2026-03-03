<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
     public function index()
    {
        $user = auth()->user();

        $currentShift = Timesheet::currentShiftForUser($user->id);

        return view('masterapp.dashboard', compact('currentShift'));
    }
    public function dashboard()
{
    $user = auth()->user();

    $currentShift = Timesheet::currentShiftForUser($user->id);

    return view('masterapp.dashboard', compact('currentShift'));
}
}
