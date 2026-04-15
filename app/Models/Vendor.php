<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Organization;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'organization_id',
        'mobile',
        'email',
        'companyname',
        'category_id',
        'address',
        'state',
        'city',
        'pin',
        'PAN',
        'gst',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'category_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(VendorBank::class, 'vendor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}