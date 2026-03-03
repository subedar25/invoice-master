<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Location extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        // 'phone',
        // 'show_map',
        // 'show_map_link',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        // 'show_map' => 'boolean',
        // 'show_map_link' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**

     * Attributes to include in audit logs.

     * Fields to include in audits.

     */
    protected $auditInclude = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
    ];



    protected $auditExclude = [
        'updated_at',
    ];


    /**
     * Validation rules for the model
     */
    // public static function rules()
    // {
    //     return [
    //         'phone' => ['nullable', 'regex:/^\+1\s\(\d{3}\)\s\d{3}-\d{4}$/'],
    //     ];
    // }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_location_link', 'location_id', 'client_id');
    }

    /**
     * Custom validation messages
     */
    // public static function messages()
    // {
    //     return [
    //         'phone.regex' => 'Phone number must be in the format +1 (123) 456-7890',
    //     ];
    // }

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source'        => request()->route()?->getName(),
        ];

        return $data;
    }
}
