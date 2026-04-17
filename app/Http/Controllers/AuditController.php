<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Exports\AuditsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;

class AuditController extends Controller
{
    /**
     * Show the audit listing with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isSystemUser = $this->isSystemUser($user);
        $organizationId = $this->resolveCurrentOrganizationId($request);

        $baseQuery = Audit::query()->with('user');
        if (! $isSystemUser) {
            if ($organizationId === null) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $this->applyOrganizationScope($baseQuery, $organizationId);
            }

            $baseQuery->whereDoesntHave('user', function ($query) {
                $query->where('user_type', 'systemuser');
            });
        }

        $query = clone $baseQuery;

        // Filters
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            // Allow partial matches (e.g. "Role" or full FQCN)
            $query->where('auditable_type', 'like', '%'.$request->auditable_type.'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $date = Carbon::parse($request->date_from)->startOfDay();
            $query->where('created_at', '>=', $date);
        }

        if ($request->filled('date_to')) {
            $date = Carbon::parse($request->date_to)->endOfDay();
            $query->where('created_at', '<=', $date);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            // search auditable_id, old_values and new_values (json cast to text)
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('auditable_id', 'like', "%{$q}%")
                    ->orWhere('old_values', 'like', "%{$q}%")
                    ->orWhere('new_values', 'like', "%{$q}%")
                    ->orWhere('auditable_type', 'like', "%{$q}%");
            });
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // some distinct values for filters
        $events = (clone $baseQuery)->select('event')->distinct()->pluck('event')->toArray();
        $auditableTypes = (clone $baseQuery)->select('auditable_type')->distinct()->pluck('auditable_type')->map(function($t){
            return class_basename($t);
        })->unique()->toArray();

        $users = User::select('id', 'first_name', 'last_name')
            ->when($organizationId !== null, function ($query) use ($organizationId) {
                $query->whereHas('organizations', function ($orgQuery) use ($organizationId) {
                    $orgQuery->where('organizations.id', $organizationId);
                });
            })
            ->when(! $isSystemUser, function ($query) {
                $query->where('user_type', '!=', 'systemuser');
            })
            ->orderBy('first_name')
            ->get();

        return view('audit.index', compact('audits','events','auditableTypes','users'));
    }

    /**
     * Export filtered audits to Excel.
     */
    public function export(Request $request)
    {
        // We'll pass the same request filters to the export class
        $filename = 'audits_export_'.now()->format('Ymd_His').'.xlsx';
        $filters = $request->all();
        $filters['__organization_id'] = $this->resolveCurrentOrganizationId($request);
        $filters['__is_system_user'] = $this->isSystemUser($request->user());

        return Excel::download(new AuditsExport($filters), $filename);
    }

    /**
     * Return audit history for a specific model (AJAX or inline include).
     * Accepts auditable_type (FQCN or class basename) and auditable_id
     */
    public function modelHistory(Request $request)
{
    // Validate request
    $request->validate([
        'auditable_type' => 'required|string',
        'auditable_id'   => 'required',
    ]);

    // Require AJAX (since your UI uses fetch)
    if (!$request->ajax()) {
        abort(400, 'Invalid request.');
    }

    $type = $request->auditable_type;

    // If user passed class short name (e.g. "User"), map it to full class path
    if (!class_exists($type)) {
        $match = Audit::where('auditable_type', 'like', '%' . $type . '%')->value('auditable_type');
        if ($match) {
            $type = $match;
        }
    }

    // Fetch history
    $auditsQuery = Audit::where('auditable_type', $type)
        ->where('auditable_id', $request->auditable_id)
        ->with('user');

    if (! $this->isSystemUser($request->user())) {
        $organizationId = $this->resolveCurrentOrganizationId($request);
        if ($organizationId === null) {
            $auditsQuery->whereRaw('1 = 0');
        } else {
            $this->applyOrganizationScope($auditsQuery, $organizationId);
        }

        $auditsQuery->whereDoesntHave('user', function ($query) {
            $query->where('user_type', 'systemuser');
        });
    }

    $audits = $auditsQuery
        ->orderBy('created_at', 'desc')
        ->get();

    return view('audit.partials.model-history', compact('audits'));
}

    private function isSystemUser(?User $user): bool
    {
        return ($user?->user_type ?? '') === 'systemuser';
    }

    private function resolveCurrentOrganizationId(Request $request): ?int
    {
        $current = $request->session()->get('current_organization_id');
        if (! empty($current)) {
            return (int) $current;
        }

        $fallback = $request->user()?->last_selected_organization_id;
        return ! empty($fallback) ? (int) $fallback : null;
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


}
 