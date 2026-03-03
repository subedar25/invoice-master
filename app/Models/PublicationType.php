<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationType extends Model
{
    protected $table = 'publication_types';

    protected $fillable = [
        'publication_type',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(PublicationType::class, 'parent_id');
    }
}
