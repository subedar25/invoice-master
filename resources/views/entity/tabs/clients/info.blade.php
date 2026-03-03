{{-- @extends('layouts.custom-admin') --}}
@section('title', 'Clients Details','bold')
{{-- @section('content') --}}
<div class="container-fluid">
    
    <div class="card">
        <div class="card-body">
        <table class="table table-bordered">
        <tbody>
        <tr>
            <th>Client Name</th><td>{{ $entity->name }}</td>
        </tr>
        <tr>
            <th>Type</th><td>{{ $entity->type }}</td>
        </tr>
        <tr>
            <th>Inline Types</th><td>{{ $entity->inline_types }}</td>
        </tr>
        <tr>
            <th>Inline Amenities</th><td>{{ $entity->inline_amenities }}</td>
        </tr>
        <tr>
            <th>Website (Pulse)</th><td>{{ $entity->website_pulse }}</td>
        </tr>
        <tr>
            <th>Website</th><td>{{ $entity->website }}</td>
        </tr>
        <tr>
            <th>Contact Name</th><td>{{ $entity->contact_name }}</td>
        </tr>
        <tr>
            <th>Contact Nickname</th><td>{{ $entity->contact_nickname }}</td>
        </tr>
        <tr>
            <th>Contact Email Nickname</th><td>{{ $entity->contact_email_nickname }}</td>
        </tr>
        <tr>
            <th>Contact Email</th><td>{{ $entity->contact_email }}</td>
        </tr>
        <tr>
            <th>Contact Phone (Office)</th><td>{{ $entity->contact_phone_office }}</td>
        </tr>
        <tr>
            <th>Contact Phone (Mobile)</th><td>{{ $entity->contact_phone_mobile }}</td>
        </tr>
        <tr>
            <th>Contact Preference</th><td>{{ $entity->contact_preference }}</td>
        </tr>
        <tr>
            <th>Billing Contact Name</th><td>{{ $entity->billing_contact_name }}</td>
        </tr>
        <tr>
            <th>Billing Contact Nickname</th><td>{{ $entity->billing_contact_nickname }}</td>
        </tr>
        <tr>
            <th>Billing Contact Email Nickname</th><td>{{ $entity->billing_contact_email_nickname }}</td>
        </tr>
        <tr>
            <th>Billing Contact Email</th><td>{{ $entity->billing_contact_email }}</td>
        </tr>
        <tr>
            <th>Billing Contact Phone (Office)</th><td>{{ $entity->billing_contact_phone_office }}</td>
        </tr>
        <tr>
            <th>Billing Contact Phone</th><td>{{ $entity->billing_contact_phone }}</td>
        </tr>
        <tr>
            <th>Billing Preference</th><td>{{ $entity->billing_preference }}</td>
        </tr>
        <tr>
            <th>Address Line 1</th><td>{{ $entity->address_line1 }}</td>
        </tr>
        <tr>
            <th>Address Line 2</th><td>{{ $entity->address_line2 }}</td>
        </tr>
        <tr>
            <th>City</th><td>{{ $entity->address_city }}</td>
        </tr>
        <tr>
            <th>State</th><td>{{ $entity->address_state }}</td>
        </tr>
        <tr>
            <th>Zipcode</th><td>{{ $entity->address_zipcode }}</td>
        </tr>
        <tr>
            <th>Country</th><td>{{ $entity->address_country }}</td>
        </tr>
        <tr>
            <th>Open</th><td>{{ $entity->open ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Active</th><td>{{ $entity->active ? 'Yes' : 'No' }}</td>
        </tr>
    <tr>
    <th>Primary Contact</th>
    <td>
        @if($entity->primaryContact)
            <a href="{{ route('entity.info', ['type'=>'users','id'=>$entity->primaryContact->id]) }}"
               class="text-primary fw-medium">
                {{ $entity->primaryContact->name }}
            </a>
        @else
            —
        @endif
    </td>
</tr>
<tr>
    <th>Primary Ad Rep</th>
<td>
@if($entity->primaryAdRep)
<a href="{{ route('entity.info', ['type'=>'users','id'=>$entity->primaryAdRep->id]) }}"
   class="text-primary fw-medium">
    {{ $entity->primaryAdRep->name }}
</a>
@else
    —
@endif
</td>
</tr>
        <tr>
            <th>Secondary Ad Rep</th>
            <td>
            @if($entity->secondaryAdRep)
                <a href="{{ route('entity.info', ['type'=>'users','id'=>$entity->secondaryAdRep->id]) }}"
                   class="text-primary fw-medium">
                    {{ $entity->secondaryAdRep->name }}
                </a>
            @else
                —
            @endif
            </td>
        </tr>
        <tr>
            <th>Newsletter Weekly Business Updates</th><td>{{ $entity->newsletter_weekly_business_updates ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Newsletter Pulse Picks</th><td>{{ $entity->newsletter_pulse_picks ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Marketing Preferences</th><td>{{ $entity->marketing_preferences }}</td>
        </tr>
        <tr>
            <th>Marketing Contact Name</th><td>{{ $entity->marketing_contact_name }}</td>
        </tr>
        <tr>
            <th>Marketing Contact Email Nickname</th><td>{{ $entity->marketing_contact_email_nickname }}</td>
        </tr>
        <tr>
            <th>Marketing Contact Email</th><td>{{ $entity->marketing_contact_email }}</td>
        </tr>
        <tr>
            <th>Marketing Contact Phone (Office)</th><td>{{ $entity->marketing_contact_phone_office }}</td>
        </tr>
        <tr>
            <th>Marketing Contact Phone (Mobile)</th><td>{{ $entity->marketing_contact_phone_mobile }}</td>
        </tr>
        <tr>
            <th>Marketing Contact Preference</th><td>{{ $entity->marketing_contact_preference }}</td>
        </tr>
        <tr>
            <th>Added Timestamp</th><td>{{ $entity->created_at }}</td>
        </tr>
        <tr>
            <th>Wisconsin Resale Number</th><td>{{ $entity->wisconsin_resale_number }}</td>
        </tr>
        <tr>
            <th>Owner Alumni School District</th><td>{{ $entity->owner_alumni_school_district }}</td>
        </tr>
    </tbody>
</table>
        </div>
    </div>
</div>
