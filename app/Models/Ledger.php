<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ledger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'invoice_id',
        'total_amount',
        'payment_method',
        'payment_type',
        'description',
        'created_date',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LedgerStatusHistory::class);
    }
}
