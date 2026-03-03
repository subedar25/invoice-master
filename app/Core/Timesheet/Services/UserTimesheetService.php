<?php

namespace App\Core\Timesheet\Services;

use App\Core\Timesheet\Contracts\TimesheetRepository;
use Carbon\Carbon;
use App\Models\User;

class UserTimesheetService
{
    public function __construct(
        private TimesheetRepository $timesheets
    ) {}

    public function calendarEvents(User $user, Carbon $start, Carbon $end)
    {
        return $this->timesheets
            ->forUserBetween($user->id, $start, $end)
            ->map(fn ($t) => [
                'id'    => $t->id,
                'title' => $t->end_time
                    ? "{$t->start_time->format('g:ia')} - {$t->end_time->format('g:ia')} ({$t->duration_hours} hrs)"
                    : 'Running',
                'start' => $t->start_time->toIso8601String(),
                'end'   => $t->end_time?->toIso8601String(),
                'color' => '#dff0d8',
            ]);
    }
   
}
