<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as BaseAudit;

class Auditable extends BaseAudit
{
    protected $table = 'audits';

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta'       => 'array',
    ];

    /**
     * User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * If auditable is a Timesheet
     */
    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class, 'auditable_id');
    }

    /**
     * If auditable is a User
     */
    public function auditableUser()
    {
        return $this->belongsTo(User::class, 'auditable_id');
    }

    public function getDescriptionAttribute(): string
    {
        return match ($this->auditable_type) {
            Timesheet::class => match ($this->event) {
                'created' => 'Timesheet created',
                'updated' => 'Timesheet updated',
                'deleted' => 'Timesheet deleted',
                'restored' => 'Timesheet restored',
                default   => ucfirst($this->event),
            },
            User::class => match ($this->event) {
                'created' => 'User created',
                'updated' => 'User updated',
                'deleted' => 'User deleted',
                'restored' => 'User restored',
                default   => ucfirst($this->event),
            },
            default => ucfirst($this->event),
        };
    }

}
