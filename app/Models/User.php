<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Contracts\Role as RoleContract;
use App\Notifications\NewUserNotification;
use App\Notifications\RoleUpdatedNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Department;
use App\Models\Permission;
use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Laragear\TwoFactor\TwoFactorAuthentication;
use Illuminate\Support\Collection;

class User extends Authenticatable implements Auditable, TwoFactorAuthenticatable
{
    use HasFactory, Notifiable, HasRoles, AuditableTrait, SoftDeletes, TwoFactorAuthentication;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'active',
        'user_type',
        'last_selected_organization_id',
        'change_password',
        'phone',
        'soft_delete',
        'department_id',
        'designation_id',
        'reporting_manager_id',
        'address',
        'city',
        'state',
        'pincode',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'change_password' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'active' => 'boolean',
        'department_id' => 'integer',
        'designation_id' => 'integer',
        'reporting_manager_id' => 'integer',
        'last_selected_organization_id' => 'integer',
    ];
    protected $dates = [
        'deleted_at'
    ];

    /**
     * Explicitly audit user-module fields.
     */
    protected $auditInclude = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'active',
        'user_type',
        'change_password',
        'department_id',
        'designation_id',
        'reporting_manager_id',
        'address',
        'city',
        'state',
        'pincode',
        'photo',
    ];

    /**
     * Avoid storing sensitive/noisy values in audits.
     */
    protected $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'updated_at',
        'last_login_at',
        'last_logout_at',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Consider only active roles for authorization checks.
     * Inactive roles stay assigned but do not grant abilities.
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        if ($roles instanceof \BackedEnum) {
            $roles = $roles->value;
        }

        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = explode('|', $roles);
        }

        $query = $this->roles()->where('roles.is_active', true);

        if ($guard !== null) {
            $query->where('guard_name', $guard);
        }

        if ($roles instanceof RoleContract) {
            return $query->whereKey($roles->getKey())->exists();
        }

        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role, $guard)) {
                    return true;
                }
            }

            return false;
        }

        if (is_numeric($roles)) {
            return $query->whereKey((int) $roles)->exists();
        }

        if (is_string($roles)) {
            return $query->where('name', $roles)->exists();
        }

        return false;
    }

    public function hasAnyRole(...$roles): bool
    {
        $roles = count($roles) === 1 && is_array($roles[0]) ? $roles[0] : $roles;

        return $this->hasRole($roles);
    }

    /**
     * Treat inactive permissions as unavailable for authorization checks.
     * Uses fresh is_active from DB so disabling one permission (e.g. delete) does not affect others (e.g. list).
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $permission = $this->filterPermission($permission, $guardName);

        // Always read is_active from DB to avoid stale cache (e.g. after toggling a permission)
        $isActive = \App\Models\Permission::where('id', $permission->getKey())->value('is_active');
        if (! (bool) $isActive) {
            return false;
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Direct permissions only grant access while active.
     */
    public function hasDirectPermission($permission): bool
    {
        $permission = $this->filterPermission($permission);

        $isActive = \App\Models\Permission::where('id', $permission->getKey())->value('is_active');
        if (! (bool) $isActive) {
            return false;
        }

        return $this->permissions()
            ->where('permissions.id', $permission->getKey())
            ->where('permissions.is_active', true)
            ->exists();
    }

    /**
     * Only consider active permissions when checking via role.
     * Ensures disabling "delete" permission does not affect "list" (or any other) permission.
     */
    protected function hasPermissionViaRole($permission): bool
    {
        if (is_a($this, \Spatie\Permission\Contracts\Role::class)) {
            return false;
        }

        $this->loadMissing('roles');

        foreach ($this->roles as $role) {
            $hasActivePermission = $role->permissions()
                ->where('permissions.id', $permission->getKey())
                ->where('permissions.is_active', true)
                ->exists();

            if ($hasActivePermission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the user has the permission through an active role that belongs to the given organization.
     */
    public function hasPermissionInOrganization(string $permissionName, ?int $organizationId): bool
    {
        if ($organizationId === null) {
            return false;
        }

        $permission = Permission::query()
            ->where('name', $permissionName)
            ->where('guard_name', 'web')
            ->first();
        if (! $permission) {
            return false;
        }

        $roles = $this->roles()
            ->where('roles.is_active', true)
            ->where('roles.organization_id', $organizationId)
            ->get();

        foreach ($roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if (! $this->hasRole($role, $guard)) {
                return false;
            }
        }

        return true;
    }
    
    public function sendNewUserNotification($password)
    {
        $this->notify(new NewUserNotification($this, $password));
    }
    public function sendRoleUpdatedNotification($oldRoles, $newRoles)
    {
        $this->notify(new RoleUpdatedNotification($this, $oldRoles, $newRoles));
    }
    
    public function isSystemUser(): bool
    {
        return ($this->user_type ?? '') === 'systemuser';
    }

    public function isActive()
    {
        return (bool) $this->active;
    }
    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }
    public function changePassword()
    {
        return $this->change_password == 1;
    }
    public function canAccessModule($moduleName)
    {
        // Assuming you have a many-to-many relationship between users and modules
        return $this->modules()->where('name', $moduleName)->exists();
    }
    public function password()
    {
        return $this->password;
    }
    
    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source' => request()->route()?->getName(),
        ];

        return $data;
    }

//     public function department()
// {
//     return $this->belongsTo(Department::class);
// }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(UserDesignation::class, 'designation_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')->withTimestamps();
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function userDocuments(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }


}
