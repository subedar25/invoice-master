<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\User;        
use App\Models\Department; 
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Role extends SpatieRole implements AuditableContract
{
    use HasFactory, AuditableTrait; 
      
    protected $fillable = [
        'name',
        'guard_name', 
        'department_id',
        'is_active',
    ];

    protected $auditInclude = [
        'name',
        'guard_name',
        'department_id',
        'is_active',
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

     public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
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
