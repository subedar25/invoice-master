<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class OrganizationType extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $table = 'client_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Explicitly audit organization type fields.
     */
    protected $auditInclude = [
        'name',
        'code',
        'description',
        'parent_id',
        'active',
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

    /**
     * Parent organization type (for hierarchy).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class, 'parent_id');
    }

    /**
     * Child organization types.
     */
    public function children(): HasMany
    {
        return $this->hasMany(OrganizationType::class, 'parent_id');
    }

    /**
     * Scope: only active (non-deleted) for use in dropdowns and multi-selects.
     */
    public function scopeActiveForDropdown($query)
    {
        return $query->where('active', true);
    }

    /**
     * Check if this type is in use (e.g. as parent of other types).
     * Used to prevent deletion when value is referenced.
     */
    public function isInUse(): bool
    {
        return $this->children()->exists();
    }
}
