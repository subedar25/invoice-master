<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Advertiser extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $table = 'advertisers';

    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Explicitly audit user-module fields.
     */
    protected $auditInclude = [
        'name',
        'code',
        'description',
        'active',
    ];

    /**
     * Avoid storing sensitive/noisy values in audits.
     */
    protected $auditExclude = [
        'updated_at',
    ];

    /**
     * Check if this advertiser is in use (referenced by other entities).
     * Prevent deletion when in use. Extend when relations exist (e.g. campaigns, issues).
     */
    public function isInUse(): bool
    {
        // Add checks when advertiser is referenced, e.g.:
        // return $this->campaigns()->exists();
        return false;
    }

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
