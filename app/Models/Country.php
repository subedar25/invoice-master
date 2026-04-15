<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Country extends Model implements AuditableContract
{
    use AuditableTrait;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $auditInclude = [
        'name',
        'code',
        'status',
    ];

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source' => request()->route()?->getName(),
        ];

        return $data;
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_id');
    }
}
