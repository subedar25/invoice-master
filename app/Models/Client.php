<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Client extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $table = 'clients';

    protected $dates = ['deleted_at', 'added_timestamp'];

    public $timestamps = false; 

    protected $fillable = [
        'name', 'description', 'phone', 'fax', 'restaurant_price_range_id',
        'year_founded', 'hours', 'logo', 'seasons_open', 'advertiser', 'excerpts',
        'facebook', 'twitter', 'instagram', 'pinterest',

        // Contact
        'contact_name', 'contact_email_nickname', 'contact_email',
        'contact_phone_office', 'contact_phone_mobile',
        'contact_preference',

        // Billing Contact
        'billing_contact_name', 
        'billing_contact_email_nickname',
        'billing_contact_email', 
        'billing_contact_phone_office',
        'billing_contact_phone_phone', 
        'billing_preference',

        // Address
        'address_line1', 
        'address_line2', 
        'address_city',
        'address_state', 
        'address_zipcode', 
        'address_country',

        // Additional metadata
        'added_timestamp',
        'type', 
        'innline_types', 
        'innline_amenities',

        // Status flags
        'open', 'active',

        // Assigned user relationships
        'primary_contact_id', 
        'primary_ad_rep_id', 
        'secondary_ad_rep_id',

        // Website
        'website_part', 
        'website',

        // Misc
        'wisconsin_resale_number',
        'owner_alumni_school_district',

        // Newsletters
        'newsletter_weekly_business_updates',
        'newsletter_pulse_picks',

        // Marketing
        'marketing_preferences',
        'marketing_contact_name',
        'marketing_contact_email_nickname',
        'marketing_contact_email',
        'marketing_contact_phone_office',
        'marketing_contact_phone_mobile',
        'marketing_contact_preference',
    ];

    protected $casts = [
        'seasons_open' => 'array',
        'advertiser' => 'array',
    ];

    protected $auditInclude = [
        'name', 'description', 'phone', 'fax', 'year_founded', 'hours',
        'seasons_open', 'advertiser', 'excerpts', 'facebook', 'twitter', 'instagram', 'pinterest',
        'contact_name', 'contact_email_nickname', 'contact_email', 'contact_phone_office', 'contact_phone_mobile', 'contact_preference',
        'billing_contact_name', 'billing_contact_email_nickname', 'billing_contact_email',
        'billing_contact_phone_office', 'billing_contact_phone_phone', 'billing_preference',
        'address_line1', 'address_line2', 'address_city', 'address_state', 'address_zipcode', 'address_country',
        'type', 'innline_types', 'innline_amenities', 'open', 'active',
        'primary_contact_id', 'primary_ad_rep_id', 'secondary_ad_rep_id',
        'website_part', 'website',
        'wisconsin_resale_number', 'owner_alumni_school_district',
        'newsletter_weekly_business_updates', 'newsletter_pulse_picks',
        'marketing_preferences', 'marketing_contact_name', 'marketing_contact_email_nickname', 'marketing_contact_email',
        'marketing_contact_phone_office', 'marketing_contact_phone_mobile', 'marketing_contact_preference',
    ];

    protected $auditExclude = [
        'logo',
        'added_timestamp',
    ];

    public function transformAudit(array $data): array
    {
        $data['meta'] = [
            'action_reason' => request()->get('reason'),
            'source'        => request()->route()?->getName(),
        ];

        return $data;
    }

    // UPDATE (inline + full update)
    public function updateClient(Request $request, Client $client)
    {
        $client->update($request->only($client->getFillable()));

        return redirect()->route('masterapp.contacts.index')
            ->with('success', 'Client updated successfully.');
    }
    public function primaryContact()
{
    return $this->belongsTo(User::class, 'primary_contact_id');
}

public function primaryAdRep()
{
    return $this->belongsTo(User::class, 'primary_ad_rep_id');
}

public function secondaryAdRep()
{
    return $this->belongsTo(User::class, 'secondary_ad_rep_id');
}

public function locationLinks(): HasMany
{
    return $this->hasMany(ClientLocationLink::class, 'client_id');
}

public function locations(): BelongsToMany
{
    return $this->belongsToMany(Location::class, 'client_location_link', 'client_id', 'location_id');
}

public function amenities(): BelongsToMany
{
    return $this->belongsToMany(ClientAmenity::class, 'client_client_amenity', 'client_id', 'client_amenity_id');
}

public function clientTypes(): BelongsToMany
{
    return $this->belongsToMany(ClientTypes::class, 'client_client_type', 'client_id', 'client_type_id');
}

public function restaurantPriceRange(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(RestaurantPriceRange::class, 'restaurant_price_range_id');
}

public function restaurantMeals(): BelongsToMany
{
    return $this->belongsToMany(RestaurantMeal::class, 'client_restaurant_meal', 'client_id', 'restaurant_meal_id');
}

}
