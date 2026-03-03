<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Core\Timesheet\Contracts\TimesheetRepository;
use App\Models\User;
use Carbon\Carbon;


class UserTimesheetController extends Controller
{
    public function index(User $user)
    {
        $this->authorize('view', $user);

        return view('masterapp.entity.tabs.users.timesheet.calendar', compact('user'));
    }

    public function calendarEvents(
        Request $request,
        User $user,
        TimesheetRepository $repo
    ) {
        // $this->authorize('view', $user); // Removed for admin access

        $start = Carbon::parse($request->start);
        $end   = Carbon::parse($request->end);

        return $repo
            ->forUserBetween($user->id, $start, $end)
            ->map(fn ($t) => [
                'id'    => $t->id,
                'title' => $t->end_time
                    ? "{$t->start_time->format('g:ia')} - {$t->end_time->format('g:ia')} ({$t->duration_hours} hrs)"
                    : 'Running',
                'start' => $t->start_time->toIso8601String(),
                'end'   => $t->end_time?->toIso8601String(),
            ]);
    }
}
