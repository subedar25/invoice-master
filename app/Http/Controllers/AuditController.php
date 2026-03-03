<?php

namespace App\Http\Controllers;

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
        $query = Audit::query()->with('user');

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
        $events = Audit::select('event')->distinct()->pluck('event')->toArray();
        $auditableTypes = Audit::select('auditable_type')->distinct()->pluck('auditable_type')->map(function($t){
            return class_basename($t);
        })->unique()->toArray();

        $users = \App\Models\User::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();

        return view('audit.index', compact('audits','events','auditableTypes','users'));
    }

    /**
     * Export filtered audits to Excel.
     */
    public function export(Request $request)
    {
        // We'll pass the same request filters to the export class
        $filename = 'audits_export_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new AuditsExport($request->all()), $filename);
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
    $audits = Audit::where('auditable_type', $type)
        ->where('auditable_id', $request->auditable_id)
        ->orderBy('created_at', 'desc')
        ->with('user')
        ->get();

    return view('audit.partials.model-history', compact('audits'));
}


}
 