<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceFile;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Timesheet;
use App\Models\User;
use OwenIt\Auditing\Models\Audit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

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
        $organizationId = isset($this->filters['__organization_id']) && $this->filters['__organization_id'] !== ''
            ? (int) $this->filters['__organization_id']
            : null;
        $isSystemUser = !empty($this->filters['__is_system_user']);

        if (! $isSystemUser) {
            if ($organizationId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $this->applyOrganizationScope($query, $organizationId);
            }

            $query->whereDoesntHave('user', function ($userQuery) {
                $userQuery->where('user_type', 'systemuser');
            });
        }

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

    private function applyOrganizationScope($query, int $organizationId): void
    {
        $query->where(function ($scoped) use ($organizationId) {
            $scoped->where(function ($q) use ($organizationId) {
                $q->where('auditable_type', Organization::class)
                    ->where('auditable_id', $organizationId);
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', Department::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new Department())->getTable())
                            ->whereColumn((new Department())->getTable().'.id', 'audits.auditable_id')
                            ->where((new Department())->getTable().'.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', Location::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new Location())->getTable())
                            ->whereColumn((new Location())->getTable().'.id', 'audits.auditable_id')
                            ->where((new Location())->getTable().'.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', Outlet::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new Outlet())->getTable())
                            ->whereColumn((new Outlet())->getTable().'.id', 'audits.auditable_id')
                            ->where((new Outlet())->getTable().'.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', Product::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new Product())->getTable())
                            ->whereColumn((new Product())->getTable().'.id', 'audits.auditable_id')
                            ->where((new Product())->getTable().'.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', Invoice::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new Invoice())->getTable())
                            ->whereColumn((new Invoice())->getTable().'.id', 'audits.auditable_id')
                            ->where((new Invoice())->getTable().'.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', InvoiceDetail::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new InvoiceDetail())->getTable().' as invoice_details')
                            ->join((new Invoice())->getTable().' as invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
                            ->whereColumn('invoice_details.id', 'audits.auditable_id')
                            ->where('invoices.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', InvoiceFile::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new InvoiceFile())->getTable().' as invoice_files')
                            ->join((new Invoice())->getTable().' as invoices', 'invoices.id', '=', 'invoice_files.invoice_id')
                            ->whereColumn('invoice_files.id', 'audits.auditable_id')
                            ->where('invoices.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', User::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from('organization_user')
                            ->whereColumn('organization_user.user_id', 'audits.auditable_id')
                            ->where('organization_user.organization_id', $organizationId);
                    });
            })->orWhere(function ($q) use ($organizationId) {
                $q->where('auditable_type', Timesheet::class)
                    ->whereExists(function ($sub) use ($organizationId) {
                        $sub->select(DB::raw(1))
                            ->from((new Timesheet())->getTable().' as timesheets')
                            ->join('organization_user', 'organization_user.user_id', '=', 'timesheets.user_id')
                            ->whereColumn('timesheets.id', 'audits.auditable_id')
                            ->where('organization_user.organization_id', $organizationId);
                    });
            });
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Event', 'Model', 'Model ID', 'User ID', 'User Name', 'Old Values', 'New Values', 'URL', 'IP', 'Created At'
        ];
    }
}
