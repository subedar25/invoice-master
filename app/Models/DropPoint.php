<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DropPoint extends Model
{
     protected $fillable = [
        'name',
        'client_id',
    ];
}
