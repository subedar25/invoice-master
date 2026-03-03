<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    use HasFactory;

    protected $table = 'user_statuses'; 

    protected $fillable = ['label'];

    public function getBadgeClassAttribute(): string
{
    return match ($this->label) {
        'Available'    => 'badge-success',
        'Available - Lunch'      => 'badge-info',
        'Available - Out of Office'       => 'badge-warning',
        'Available - Remote'     => 'badge-success',
        'Do Not Disturb'  => 'badge-danger',
        'Lunch' => 'badge-info',
        'Not Available' => 'badge-secondary',
        default         => 'badge-secondary',
    };
}

}
?>