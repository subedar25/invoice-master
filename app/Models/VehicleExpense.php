<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class VehicleExpense extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'file_id',
        'total',
        'mileage',
        'type',
        'notes',
        'date',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
    ];
    public function vehicle()
{
    return $this->belongsTo(Vehicle::class);
}

public function file()
{
    return $this->belongsTo(File::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
public function expenses()
{
    return $this->hasMany(\App\Models\VehicleExpense::class);
}
//create vehicle expense relationship
public function vehicleExpense()
{
    return $this->belongsTo(VehicleExpense::class);
}
//single delete function
public static function bulkDelete($ids)
{
    return self::whereIn('id', $ids)->delete(); 
}
// bulk delete function
public static function deleteExpenses($ids)
{
    return self::whereIn('id', $ids)->delete();
}
public function createExpense(User $user, Vehicle $vehicle)
{
    return $user->can('vehicle.expense.create');
}

}