<?php

namespace App\Exports;

use OwenIt\Auditing\Models\Audit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AuditsExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Audit::query()->with('user');

        if (!empty($this->filters['event'])) {
            $query->where('event', $this->filters['event']);
        }

        if (!empty($this->filters['auditable_type'])) {
            $query->where('auditable_type', 'like', '%'.$this->filters['auditable_type'].'%');
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from'].' 00:00:00');
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to'].' 23:59:59');
        }

        if (!empty($this->filters['q'])) {
            $q = $this->filters['q'];
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('auditable_id', 'like', "%{$q}%")
                    ->orWhere('old_values', 'like', "%{$q}%")
                    ->orWhere('new_values', 'like', "%{$q}%")
                    ->orWhere('auditable_type', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        $export = $items->map(function ($a) {
            return [
                'id' => $a->id,
                'event' => $a->event,
                'auditable_type' => class_basename($a->auditable_type),
                'auditable_id' => $a->auditable_id,
                'user_id' => $a->user_id,
                'user_name' => $a->user ? trim($a->user->first_name . ' ' . $a->user->last_name) : null,
                'old_values' => is_array($a->old_values) ? json_encode($a->old_values) : $a->old_values,
                'new_values' => is_array($a->new_values) ? json_encode($a->new_values) : $a->new_values,
                'url' => $a->url ?? null,
                'ip_address' => $a->ip_address ?? null,
                'created_at' => $a->created_at ? $a->created_at->toDateTimeString() : null,
            ];
        });

        return collect($export);
    }

    public function headings(): array
    {
        return [
            'ID', 'Event', 'Model', 'Model ID', 'User ID', 'User Name', 'Old Values', 'New Values', 'URL', 'IP', 'Created At'
        ];
    }
}
