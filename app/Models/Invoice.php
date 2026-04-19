<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Invoice extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'invoice_number',
        'organization_id',
        'outlet_id',
        'vendor_id',
        'createdby_id',
        'department_id',
        'pay_term',
        'comp_date',
        'year',
        'description',
        'total_amount',
        'paid_amount',
        'status',
        'order_status',
        'task_status',
        'priority',
    ];

    protected $auditInclude = [
        'invoice_number',
        'organization_id',
        'outlet_id',
        'vendor_id',
        'createdby_id',
        'department_id',
        'pay_term',
        'comp_date',
        'year',
        'description',
        'total_amount',
        'paid_amount',
        'status',
        'order_status',
        'task_status',
        'priority',
    ];

    protected $casts = [
        'comp_date' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(InvoiceFile::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }

    public function ledgerStatusHistories(): HasMany
    {
        return $this->hasMany(LedgerStatusHistory::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class);
    }

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source' => request()->route()?->getName(),
        ];

        return $data;
    }
}
