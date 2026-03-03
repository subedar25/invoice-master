<?php

namespace App\Core\Timesheet\Contracts;

use App\Models\Timesheet;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use App\Models\User;
interface TimesheetRepository
{
    public function hasOpenShift(int $userId): bool;

    public function getCurrentShift(int $userId): ?Timesheet;

    public function create(array $data): Timesheet;

    public function update(Timesheet $timesheet, array $data): Timesheet;

    public function forUserBetween(int $userId, $start, $end): Collection;

    public function delete(int $id): void;

    public function find(int $id): Timesheet;

    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'added_timestamp', string $sortDir = 'desc');
    
    public function updateByFilter(array $filters, array $data);



    
}
