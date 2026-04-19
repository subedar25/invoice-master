<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'type',
    ];

    protected $auditInclude = [
        'name',
        'slug',
        'display_name',
        'module_id',
        'guard_name',
        'is_active',
        'type',
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

    /**
     * Permissions that can be assigned to roles by the given user.
     * Non–system users only see public module + public permission types; system users see all.
     */
    public function scopeAssignableForViewer(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if ($user instanceof User && $user->isSystemUser()) {
            return $query;
        }

        return $query->where(function (Builder $q) {
            $q->where('permissions.type', 'public')
                ->orWhereNull('permissions.type');
        })->whereHas('module', function (Builder $mq) {
            $mq->where(function (Builder $q) {
                $q->where('modules.type', 'public')
                    ->orWhereNull('modules.type');
            });
        });
    }

    public static function assignablePermissionIdsFor(?User $user = null): array
    {
        return static::query()
            ->assignableForViewer($user)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isAssignableForViewer(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if ($user instanceof User && $user->isSystemUser()) {
            return true;
        }

        if (($this->type ?? 'public') !== 'public') {
            return false;
        }

        $module = $this->relationLoaded('module') ? $this->module : $this->module()->first();

        return $module && (($module->type ?? 'public') === 'public');
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
