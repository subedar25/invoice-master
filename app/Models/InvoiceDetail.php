<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class InvoiceDetail extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_name',
        'hsn',
        'cgst',
        'sgst',
        'total_gst',
        'quantity',
        'unit_price',
        'total_price',
        'total_amount',
        'product_desciption',
        'discount',
        'dis_comment',
    ];

    protected $auditInclude = [
        'invoice_id',
        'product_id',
        'product_name',
        'hsn',
        'cgst',
        'sgst',
        'total_gst',
        'quantity',
        'unit_price',
        'total_price',
        'total_amount',
        'product_desciption',
        'discount',
        'dis_comment',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
