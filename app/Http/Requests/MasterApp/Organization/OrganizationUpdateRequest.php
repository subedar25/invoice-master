<?php

namespace App\Http\Requests\MasterApp\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $organizationId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('clients', 'name')->ignore($organizationId)],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:12', 'regex:/^(\d{10}|\d{3}-\d{3}-\d{4})?$/'],
            'fax' => ['nullable', 'string', 'max:12', 'regex:/^(\d{10}|\d{3}-\d{3}-\d{4})?$/'],
            'year_founded' => ['nullable', 'digits:4'],
            'hours' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'logo_remove' => ['nullable', 'boolean'],
            'seasons_open' => ['nullable', 'array'],
            'seasons_open.*' => ['integer', 'exists:seasons,id'],
            'excerpts' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'pinterest' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email_nickname' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone_office' => ['nullable', 'string', 'max:50'],
            'contact_phone_mobile' => ['nullable', 'string', 'max:50'],
            'contact_preference' => ['nullable', Rule::in(['email', 'phone', 'text', 'no_preference', 'see_notes'])],
            'billing_contact_name' => ['nullable', 'string', 'max:255'],
            'billing_contact_email_nickname' => ['nullable', 'string', 'max:255'],
            'billing_contact_email' => ['nullable', 'email', 'max:255'],
            'billing_contact_phone_office' => ['nullable', 'string', 'max:50'],
            'billing_contact_phone_phone' => ['nullable', 'string', 'max:50'],
            'billing_preference' => ['nullable', Rule::in(['email', 'mail'])],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_state' => ['nullable', 'string', 'max:100'],
            'address_zipcode' => ['nullable', 'string', 'max:20'],
            'address_country' => ['nullable', 'string', 'max:100'],
            'physical_location_id' => ['nullable', 'exists:locations,id'],
            'mailing_location_id' => ['nullable', 'exists:locations,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'type' => ['nullable', 'string', 'max:255'],
            'innline_types' => ['nullable', 'string', 'max:255'],
            'innline_amenities' => ['nullable', 'string', 'max:255'],
            'open' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'primary_contact_id' => ['nullable', 'exists:users,id'],
            'primary_ad_rep_id' => ['nullable', 'exists:users,id'],
            'secondary_ad_rep_id' => ['nullable', 'exists:users,id'],
            'website_part' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'wisconsin_resale_number' => ['nullable', 'string', 'max:255'],
            'owner_alumni_school_district' => ['nullable', 'string', 'max:255'],
            'newsletter_weekly_business_updates' => ['nullable', 'boolean'],
            'newsletter_pulse_picks' => ['nullable', 'boolean'],
            'marketing_preferences' => ['nullable', Rule::in(['all_marketing', 'no_marketing'])],
            'marketing_contact_name' => ['nullable', 'string', 'max:255'],
            'marketing_contact_email_nickname' => ['nullable', 'string', 'max:255'],
            'marketing_contact_email' => ['nullable', 'email', 'max:255'],
            'marketing_contact_phone_office' => ['nullable', 'string', 'max:50'],
            'marketing_contact_phone_mobile' => ['nullable', 'string', 'max:50'],
            'marketing_contact_preference' => ['nullable', Rule::in(['email', 'phone', 'text', 'no_preference', 'see_notes'])],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:client_amenities,id'],
            'restaurant_price_range_id' => ['nullable', 'integer', 'exists:restaurant_price_ranges,id'],
            'restaurant_meal_ids' => ['nullable', 'array'],
            'restaurant_meal_ids.*' => ['integer', 'exists:restaurant_meals,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Organization name already exists.',
            'phone.regex' => 'Phone must be 10 digits in US format (e.g. 123-456-7890).',
            'fax.regex' => 'Fax must be 10 digits in US format (e.g. 123-456-7890).',
        ];
    }
}
