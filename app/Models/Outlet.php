<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Outlet extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'organization_id',
        'location_id',
        'area_manager_id',
        'address',
        'state_id',
        'city',
        'country_id',
        'pincode',
        'photo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Fields to include in audits.
     */
    protected $auditInclude = [
        'name',
        'organization_id',
        'location_id',
        'area_manager_id',
        'address',
        'state_id',
        'city',
        'country_id',
        'pincode',
        'photo',
        'status',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function areaManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'area_manager_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
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