<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use App\Core\Timesheet\Services\TimesheetService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class TimesheetClockController extends Controller
{
    public function clockIn(Request $request, TimesheetService $service)
    {
        $request->validate([
            'clock_in_mode' => [
                'required',
                Rule::in(['office', 'remote', 'out_of_office', 'do_not_disturb']),
            ],
        ]);

        $timesheet = $service->clockIn(
            auth()->id(),
            $request->clock_in_mode
        );

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'timesheet_id' => $timesheet->id,
            // 'shift' => $timesheet,
        ]);
    }

    public function clockOut(Request $request, TimesheetService $service)
    {
        $service->clockOut(
            auth()->id(),
            $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
        ]);
    }

    public function resumeFromLunch(Request $request, TimesheetService $service)
    {
        $request->validate([
            'clock_in_mode' => [
                'required',
                Rule::in(['office', 'remote', 'out_of_office', 'do_not_disturb']),
            ],
        ]);

        $service->resumeFromLunch(auth()->id(), $request->clock_in_mode);

        return response()->json([
            'success' => true,
            'message' => 'Resumed from lunch',
        ]);
    }
}
