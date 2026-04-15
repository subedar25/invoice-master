<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorBank extends Model
{
    use SoftDeletes;

    protected $table = 'vendor_bank';

    protected $fillable = [
        'vendor_id', 'bank_name', 'ac_number', 'ifsc_number', 'ac_type'
    ];
}