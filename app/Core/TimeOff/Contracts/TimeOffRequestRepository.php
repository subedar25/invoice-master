<?php

namespace App\Core\TimeOff\Contracts;

interface TimeOffRequestRepository
{
    public function getRequests(array $filters = [], int $perPage = 10);
    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'added_timestamp', string $sortDir = 'desc');
    public function countRequests(array $filters = [], ?string $search = null);
    public function updateByFilter(array $filters, array $data);
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function updateMultiple(array $ids, array $data);
    public function delete(int $id);
}
