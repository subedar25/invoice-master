<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class InvoiceFile extends Model implements AuditableContract
{
    use AuditableTrait;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id', 'filename', 'created_at'
    ];

    protected $auditInclude = [
        'invoice_id',
        'filename',
        'created_at',
    ];

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source' => request()->route()?->getName(),
        ];

        return $data;
    }
}
