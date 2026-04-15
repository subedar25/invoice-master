<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_categories';

    protected $fillable = [
        'organization_id',
        'department_id',
        'name',
        'desc',
        'parent_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'category_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(VendorCategory::class, 'parent_id');
    }
}