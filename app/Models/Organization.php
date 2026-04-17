<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Organization extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'organizations';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (Organization $organization) {
            if ($organization->created_date === null) {
                $organization->created_date = now();
            }
        });

        static::updating(function (Organization $organization) {
            $organization->edited_date = now();
        });
    }

    protected $fillable = [
        'name',
        'status',
        'invoice_prefix',
        'logo',
        'created_date',
        'edited_date',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_date' => 'datetime',
        'edited_date' => 'datetime',
    ];

    protected $auditInclude = [
        'name',
        'status',
        'invoice_prefix',
        'logo',
        'created_date',
        'edited_date',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'organization_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'organization_id');
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class, 'organization_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'organization_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'organization_id');
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
