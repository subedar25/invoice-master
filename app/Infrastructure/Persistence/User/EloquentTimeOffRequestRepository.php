<?php

namespace App\Infrastructure\Persistence\User;

use App\Core\TimeOff\Contracts\TimeOffRequestRepository;
use App\Models\TimeOffRequest;

class EloquentTimeOffRequestRepository implements TimeOffRequestRepository
{
    public function getForDataTable(array $filters = [], ?string $search = null, int $start = 0, int $length = 10, string $sortColumn = 'added_timestamp', string $sortDir = 'desc')
    {
        $query = $this->buildQuery($filters, $search);

        // Ensure valid columns for sorting
        $allowedSorts = ['id', 'user_id', 'start_time', 'end_time', 'added_timestamp', 'status', 'paid'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDir);
        } else {
            $query->orderBy('added_timestamp', 'desc');
        }

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        return $query->get();
    }

    public function countRequests(array $filters = [], ?string $search = null)
    {
        return $this->buildQuery($filters, $search)->count();
    }

    private function buildQuery(array $filters = [], ?string $search = null)
    {
        $query = TimeOffRequest::with('user');

        // ID Range Filter
        if (!empty($filters['id_from'])) {
            $query->where('id', '>=', $filters['id_from']);
        }
        if (!empty($filters['id_to'])) {
            $query->where('id', '<=', $filters['id_to']);
        }

        // Date Range Filter (Start Time)
        if (!empty($filters['date_from'])) {
            $query->whereDate('start_time', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('start_time', '<=', $filters['date_to']);
        }

        // Status Filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // User ID Filter (Crucial for Ownership enforcement)
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
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

    public function getRequests(array $filters = [], int $perPage = 10)
    {
        // Re-using buildQuery for consistency (though simpler strict filters usually apply here)
        $query = $this->buildQuery($filters);

        $sortColumn = $filters['sort_by'] ?? 'added_timestamp';
        $sortDirection = $filters['sort_dir'] ?? 'desc';
        
        $allowedSorts = ['id', 'user_id', 'start_time', 'end_time', 'added_timestamp', 'status', 'paid'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('added_timestamp', 'desc');
        }

        if ($perPage > 0) {
            return $query->paginate($perPage)->withQueryString();
        }
        
        return $query->get();
    }

    public function updateByFilter(array $filters, array $data)
    {
        // Re-use buildQuery but without eager loading needed for update
        $query = TimeOffRequest::query();

        if (!empty($filters['id_from'])) $query->where('id', '>=', $filters['id_from']);
        if (!empty($filters['id_to'])) $query->where('id', '<=', $filters['id_to']);
        if (!empty($filters['date_from'])) $query->whereDate('start_time', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('start_time', '<=', $filters['date_to']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['user_id'])) $query->where('user_id', $filters['user_id']);

        return $query->update($data);
    }

    public function all()
    {
        // Fallback or deprecated, use getRequests
        return $this->getRequests([], 0);
    }

    public function find(int $id)
    {
        return TimeOffRequest::findOrFail($id);
    }

    public function create(array $data)
    {
        return TimeOffRequest::create($data);
    }

    public function update(int $id, array $data)
    {
        $request = $this->find($id);
        $request->update($data);
        return $request;
    }

    public function updateMultiple(array $ids, array $data)
    {
        return TimeOffRequest::whereIn('id', $ids)->update($data);
    }

    public function delete(int $id)
    {
        $request = $this->find($id);
        return $request->delete();
    }
}
