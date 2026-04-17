<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDesignation extends Model
{
    protected $table = 'user_designation';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
