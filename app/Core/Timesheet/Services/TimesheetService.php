<?php

namespace App\Core\Timesheet\Services;

use App\Core\Timesheet\Contracts\TimesheetRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Requests\MasterApp\Timesheet\TimesheetStoreRequest;
use App\Http\Requests\MasterApp\Timesheet\TimesheetUpdateRequest;
use App\Models\Timesheet;
use App\Models\User;
class TimesheetService
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

        // Lunch: keep shift open, only switch mode to "lunch" so dashboard shows "Lunch"
        if ($reason === 'lunch') {
            return $this->timesheets->update($timesheet, ['clock_in_mode' => 'lunch']);
        }

        $data = ['end_time' => now()];
        return $this->timesheets->update($timesheet, $data);
    }

    /**
     * Resume from lunch: set current shift's clock_in_mode back to office/remote/etc.
     */
    public function resumeFromLunch(int $userId, string $mode): Timesheet
    {
        $timesheet = $this->timesheets->getCurrentShift($userId);

        if (!$timesheet) {
            throw ValidationException::withMessages([
                'clock_in' => 'No active shift found.',
            ]);
        }

        if (($timesheet->clock_in_mode ?? '') !== 'lunch') {
            throw ValidationException::withMessages([
                'clock_in' => 'You are not on lunch break.',
            ]);
        }

        return $this->timesheets->update($timesheet, ['clock_in_mode' => $mode]);
    }
    public function createTimesheet(array $data)
    {
        // Normalize empty end_time
        if (empty($data['end_time'])) {
            $data['end_time'] = null;
        }

        // Default type if not passed
        $data['type'] ??= 'normal_paid';

        return $this->timesheets->create($data);
    }
    public function updateTimesheet(int $id, array $data): Timesheet
    {
        $timesheet = $this->timesheets->find($id);
        return $this->timesheets->update($timesheet, $data);
    }

    public function update(int $id, array $data): Timesheet
    {
        $timesheet = $this->timesheets->find($id);
        return $this->timesheets->update($timesheet, $data);
    }

    public function delete(int $id): void
    {
        $this->timesheets->delete($id);
    }

    public function create(array $data): Timesheet
    {
        return $this->timesheets->create($data);
    }

    public function getDataTableData(array $filters, ?string $search, int $start, int $length, array $order)
    {
        $sortColumn = $order['column'] ?? 'start_time';
        $sortDir = $order['dir'] ?? 'desc';

        $data = $this->timesheets->getForDataTable($filters, $search, $start, $length, $sortColumn, $sortDir);
        $totalDisplay = $this->timesheets->countTimesheets($filters, $search);
        $totalAll = $this->timesheets->countTimesheets([], null);

        return [
            'data' => $data,
            'recordsFiltered' => $totalDisplay, // DataTables expects this for pagination
            'recordsTotal' => $totalAll,
        ];
    }

    public function getAllUsers()
    {
        return $this->timesheets->getAllUsers();
    }

    public function paginateVisibleToUser(User $viewer, int $perPage = 20)
    {
        return $this->timesheets->paginateVisibleToUser($viewer, $perPage);
    }

    public function getUsersOrderedByFirstName()
    {
        return $this->timesheets->getUsersOrderedByFirstName();
    }

    public function find(int $id): Timesheet
    {
        return $this->timesheets->find($id);
    }

    public function getAdminUsersForNotifications()
    {
        return $this->timesheets->getAdminUsersForNotifications();
    }
}
