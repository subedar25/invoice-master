<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;          
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\VehicleExpense;
use App\Models\File;

class Vehicle extends Model
{
    use SoftDeletes, HasFactory;                 

    protected $fillable = [
        'vin',
        'description',
        'active',
        'hitch',
        'driver_side_sponsor',
        'passenger_side_sponsor'
    ];

    // Add relationships if needed
public function expenses()
{
    return $this->hasMany(VehicleExpense::class);
}
public function file()
{
    return $this->belongsTo(File::class, 'file_id');

}
public function permissions()
    {
        return $this->morphMany(Permission::class, 'model');
    }
    public function roles()
    {
        return $this->morphMany(Role::class, 'model');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
    public function files()
    {
        return $this->hasMany(File::class, 'vehicle_id');
    }
    public function addFile(File $file)
    {
        $this->files()->save($file);
    }
    public function removeFile(File $file)
    {
        $this->files()->where('id', $file->id)->delete();
    }
    public function getActiveStatusAttribute()
    {
        return $this->active ? 'Active' : 'Inactive';
    }
    //create vehicle expense relationship
    public function vehicleExpenses()
    {
        return $this->hasMany(VehicleExpense::class);
    }
    public function addVehicleExpense(VehicleExpense $expense)
    {
        $this->vehicleExpenses()->save($expense);
    }
    public function removeVehicleExpense(VehicleExpense $expense)
    {
        $this->vehicleExpenses()->where('id', $expense->id)->delete();
    }
    
}
