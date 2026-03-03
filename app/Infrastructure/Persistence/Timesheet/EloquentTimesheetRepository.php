<?php

namespace App\Infrastructure\Persistence\Timesheet;

use App\Core\Timesheet\Contracts\TimesheetRepository;
use App\Models\Timesheet;
use Illuminate\Support\Collection;

class EloquentTimesheetRepository implements TimesheetRepository
{
    public function hasOpenShift(int $userId): bool
    {
        return Timesheet::where('user_id', $userId)
            ->whereNull('end_time')
            ->exists();
    }

    public function getCurrentShift(int $userId): ?Timesheet
    {
        return Timesheet::where('user_id', $userId)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();
    }

    public function create(array $data): Timesheet
    {
        return Timesheet::create($data);
    }

    public function update(Timesheet $timesheet, array $data): Timesheet
    {
        $timesheet->update($data);
        return $timesheet;
    }

    public function forUserBetween(int $userId, $start, $end): Collection
    {
        return Timesheet::where('user_id', $userId)
            ->whereBetween('start_time', [$start, $end])
            ->orderBy('start_time')
            ->get();
    }
    // public function delete(int $id): void
    // {
    //     Timesheet::findOrFail($id)->delete();
    // }
    public function find(int $id): Timesheet
    {
        return Timesheet::findOrFail($id);
    }

    public function delete(int $id): void
    {
        Timesheet::whereKey($id)->delete(); // soft delete
    }

    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'start_time', string $sortDir = 'desc')
    {
        $query = $this->buildQuery($filters, $search);

        // Ensure valid columns for sorting
        $allowedSorts = ['id', 'start_time', 'end_time', 'user_id', 'type', 'clock_in_mode'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDir);
        } else {
            $query->orderBy('start_time', 'desc');
        }

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        return $query->get();
    }

    public function countTimesheets(array $filters = [], ?string $search = null)
    {
        return $this->buildQuery($filters, $search)->count();
    }

    private function buildQuery(array $filters = [], ?string $search = null)
    {
        $query = Timesheet::with('user');

        // User Filter
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Date Range Filter (Start Time)
        if (!empty($filters['date_from'])) {
            $query->whereDate('start_time', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('start_time', '<=', $filters['date_to']);
        }

        // Type Filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Global Search (DataTables Search Box)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    public function updateByFilter(array $filters, array $data)
    {
        $query = Timesheet::query();

        // Apply the same filters as buildQuery
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('start_time', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('start_time', '<=', $filters['date_to']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->update($data);
    }
}
