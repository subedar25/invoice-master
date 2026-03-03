<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientTypes extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'client_types';

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'status',
        'display_order',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ClientTypes::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ClientTypes::class, 'parent_id')->orderBy('display_order')->orderBy('name');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_client_type', 'client_type_id', 'client_id');
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(ClientAmenity::class, 'client_type_id')->orderBy('name');
    }
}
