<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpAccount extends Model
{
    protected $fillable = [
    'smtp_from',
    'smtp_from_name',
    'smtp_host',
    'smtp_password',
    'smtp_port',
    'smtp_secure', 
    'smtp_username',
];
}
