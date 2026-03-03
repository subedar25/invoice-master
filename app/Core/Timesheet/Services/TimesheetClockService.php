<?php

namespace App\Core\Timesheet\Services;

use App\Core\Timesheet\Contracts\TimesheetRepository;
use Illuminate\Validation\ValidationException;

class TimesheetClockService
{
    public function __construct(
        private TimesheetRepository $timesheets
    ) {}

    public function clockIn(int $userId, string $mode)
    {
        if ($this->timesheets->hasOpenShift($userId)) {
            throw ValidationException::withMessages([
                'clock_in' => 'You already have an active shift.',
            ]);
        }

        return $this->timesheets->create([
            'user_id'       => $userId,
            'start_time'    => now(),
            'end_time'      => null,
            'clock_in_mode' => $mode,
            'type'          => 'normal_paid',
        ]);
    }

    public function clockOut(int $userId, ?string $reason = null)
    {
        $timesheet = $this->timesheets->getCurrentShift($userId);

        if (!$timesheet) {
            throw ValidationException::withMessages([
                'clock_out' => 'No active shift found.',
            ]);
        }

        $data = ['end_time' => now()];

        if ($reason === 'lunch') {
            $data['type'] = 'absent_unpaid';
        }

        return $this->timesheets->update($timesheet, $data);
    }
}
