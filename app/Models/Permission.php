<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as SpatiePermission;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends SpatiePermission implements AuditableContract
{
    use HasFactory, AuditableTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'module_id',
        'guard_name',
        'is_active',
    ];

    protected $auditInclude = [
        'name',
        'slug',
        'display_name',
        'module_id',
        'guard_name',
        'is_active',
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the module that owns the permission.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source'        => request()->route()?->getName(),
        ];

        return $data;
    }
}
