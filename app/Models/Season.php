<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Season extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $table = 'seasons';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Explicitly audit user-module fields.
     */
    protected $auditInclude = [
        'name',
        'code',
        'description',
        'status'
    ];

    /**
     * Avoid storing sensitive/noisy values in audits.
     */
    protected $auditExclude = [
        'updated_at',
    ];

    public function transformAudit(array $data): array
    {
        $request = request();
        $data['meta'] = [
            'action_reason' => $request ? $request->get('reason') : null,
            'source'        => $request && $request->route() ? $request->route()->getName() : null,
        ];

        return $data;
    }
}
