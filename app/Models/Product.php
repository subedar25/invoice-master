<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Product extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'unit_price',
        'hsn',
        'cgst',
        'sgst',
        'total_gst',
        'final_price',
        'organization_id',
        'status',
    ];

    protected $auditInclude = [
        'name',
        'unit_price',
        'hsn',
        'cgst',
        'sgst',
        'total_gst',
        'final_price',
        'organization_id',
        'status',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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
