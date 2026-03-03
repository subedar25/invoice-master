<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class TimeOffRequest extends Model implements AuditableContract
{
    use HasFactory, AuditableTrait;

    // Standard 'id' is default, so no need to specify $primaryKey

    public $timestamps = true; // Enable timestamps handling for creation
    const CREATED_AT = 'added_timestamp';
    const UPDATED_AT = null; // Disable updated_at

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'paid',
        'notes',
        'submitted',
        'status',
        'timesheet_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'added_timestamp' => 'datetime',
        'paid' => 'boolean',
        'submitted' => 'boolean',
    ];

    /**
     * Fields to include in audits.
     */
    protected $auditInclude = [
        'user_id',
        'start_time',
        'end_time',
        'paid',
        'notes',
        'status',
        'timesheet_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

