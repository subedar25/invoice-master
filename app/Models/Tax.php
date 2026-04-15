<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Tax extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'tax_name',
        'tax_value',
        'tax_status',
    ];

    protected $casts = [
        'tax_value' => 'decimal:2',
        'tax_status' => 'boolean',
    ];

    protected $auditInclude = [
        'tax_name',
        'tax_value',
        'tax_status',
    ];

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source' => request()->route()?->getName(),
        ];

        return $data;
    }
}
