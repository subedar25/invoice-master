<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'google_drive_id',
        'google_drive_md5',
        'name',
        'notes',
        'file_name',
        'file_type',
        'added_timestamp',
        'added_by_user',
        'slug',
    ];

    protected $casts = [
        'added_timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by_user');
    }
       public function uploader()
    {
        return $this->belongsTo(User::class, 'added_by_user');
    }
    public function vehicleExpenses()
    {
        return $this->hasMany(VehicleExpense::class, 'file_id');
    }
    public function getUrlAttribute()
    {
        return route('files.download', $this->slug);
    }
    public function getThumbnailUrlAttribute()
    {
        // Assuming you have a method to generate thumbnail URL from Google Drive ID
        return $this->google_drive_id ? "https://drive.google.com/thumbnail?id={$this->google_drive_id}" : null;
    }
    public function getDownloadUrlAttribute()
    {
        return route('files.download', $this->slug);
    }
    public function getPreviewUrlAttribute()
    {
        return $this->google_drive_id ? "https://drive.google.com/file/d/{$this->google_drive_id}/preview" : null;
    }
   //file upload
   public static function createFromUpload($uploadedFile)
{
    return self::create([
        'name'        => $uploadedFile->getClientOriginalName(),
        'file_name'   => $uploadedFile->getClientOriginalName(),
        'file_type'   => $uploadedFile->getClientMimeType(),
        'added_by_user' => auth()->id(),
        'slug'        => \Str::uuid(),
    ]);
}

}
