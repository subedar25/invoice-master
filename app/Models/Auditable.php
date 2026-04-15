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
            Organization::class => match ($this->event) {
                'created' => 'Organization created',
                'updated' => 'Organization updated',
                'deleted' => 'Organization deleted',
                'restored' => 'Organization restored',
                default   => ucfirst($this->event),
            },
            Outlet::class => match ($this->event) {
                'created' => 'Outlet created',
                'updated' => 'Outlet updated',
                'deleted' => 'Outlet deleted',
                'restored' => 'Outlet restored',
                default   => ucfirst($this->event),
            },
            Product::class => match ($this->event) {
                'created' => 'Product created',
                'updated' => 'Product updated',
                'deleted' => 'Product deleted',
                'restored' => 'Product restored',
                default   => ucfirst($this->event),
            },
            Tax::class => match ($this->event) {
                'created' => 'Tax created',
                'updated' => 'Tax updated',
                'deleted' => 'Tax deleted',
                'restored' => 'Tax restored',
                default   => ucfirst($this->event),
            },
            Invoice::class => match ($this->event) {
                'created' => 'Invoice created',
                'updated' => 'Invoice updated',
                'deleted' => 'Invoice deleted',
                'restored' => 'Invoice restored',
                default   => ucfirst($this->event),
            },
            InvoiceDetail::class => match ($this->event) {
                'created' => 'Invoice item created',
                'updated' => 'Invoice item updated',
                'deleted' => 'Invoice item deleted',
                'restored' => 'Invoice item restored',
                default   => ucfirst($this->event),
            },
            InvoiceFile::class => match ($this->event) {
                'created' => 'Invoice file added',
                'updated' => 'Invoice file updated',
                'deleted' => 'Invoice file deleted',
                'restored' => 'Invoice file restored',
                default   => ucfirst($this->event),
            },
            default => ucfirst($this->event),
        };
    }

}
