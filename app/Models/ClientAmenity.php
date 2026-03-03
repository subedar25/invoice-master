<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientAmenity extends Model
{
    protected $table = 'client_amenities';

    protected $fillable = ['name', 'client_type_id'];

    public function clientType(): BelongsTo
    {
        return $this->belongsTo(ClientTypes::class, 'client_type_id');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_client_amenity', 'client_amenity_id', 'client_id');
    }
}
