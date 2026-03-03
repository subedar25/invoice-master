@extends('layouts.custom-admin')
@section('title', 'Clients','bold')
@section('content')

<div class="container-fluid">
    <div class="card">
        <div class="card-body">

            <table id="clientsTable" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        {{-- id hidden (not shown) --}}
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Contact Name</th>
                        <th>Contact Email Nickname</th>
                        <th>Contact Email</th>
                        <th>Contact Phone Office</th>
                        <th>Contact Phone Mobile</th>
                        <th>Billing Contact Name</th>
                        <th>Billing Contact Email Nickname</th>
                        <th>Billing Contact Email</th>
                        <th>Billing Contact Phone Office</th>
                        <th>Billing Contact Phone Phone</th>
                        <th>Added Timestamp</th>
                        <th>Address Line1</th>
                        <th>Address Line2</th>
                        <th>Address City</th>
                        <th>Address State</th>
                        <th>Address Zipcode</th>
                        <th>Address Country</th>
                        <th>Contact Preference</th>
                        <th>Billing Preference</th>
                        <th>Type</th>
                        <th>Innline Types</th>
                        <th>Innline Amenities</th>
                        <th>Open</th>
                        <th>Active</th>
                        <th>Primary Contact</th>
                        <th>Primary Ad Rep</th>
                        <th>Secondary Ad Rep</th>
                        <th>Website (PULSE)</th>
                        <th>Website</th>
                        <th>Wisconsin Resale Number</th>
                        <th>Owner Alumni School District</th>
                        <th>Newsletter Weekly Business Updates</th>
                        <th>Newsletter Pulse Picks</th>
                        <th>Marketing Preferences</th>
                        <th>Marketing Contact Name</th>
                        <th>Marketing Contact Email Nickname</th>
                        <th>Marketing Contact Email</th>
                        <th>Marketing Contact Phone Office</th>
                        <th>Marketing Contact Phone Mobile</th>
                        <th>Marketing Contact Preference</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($clients as $client)
                    <tr id="client-row-{{ $client->id }}" data-id="{{ $client->id }}">
                        <td><input type="checkbox" class="row-check"></td>

                        {{-- name --}}
                        {{-- <td class="inline-edit" data-field="name">{{ $client->name }}</td> --}}
                        <td class="inline-edit" data-field="name">
                        <a href="{{ route('entity.info', ['type' => 'clients', 'id' => $client->id]) }}" class="entity-link">
                        {{ $client->name }}
                        </a>
                    {{-- <i class="fa fa-pen edit-icon ms-2 text-muted" title="Edit"></i> --}}
                        </td>


                        {{-- phone --}}
                        <td class="inline-edit" data-field="phone">{{ $client->phone }}</td>

                        {{-- contact fields --}}
                        <td class="inline-edit" data-field="contact_name">{{ $client->contact_name }}</td>
                        <td class="inline-edit" data-field="contact_email_nickname">{{ $client->contact_email_nickname }}</td>
                        <td class="inline-edit" data-field="contact_email">{{ $client->contact_email }}</td>
                        <td class="inline-edit" data-field="contact_phone_office">{{ $client->contact_phone_office }}</td>
                        <td class="inline-edit" data-field="contact_phone_mobile">{{ $client->contact_phone_mobile }}</td>

                        {{-- billing --}}
                        <td class="inline-edit" data-field="billing_contact_name">{{ $client->billing_contact_name }}</td>
                        <td class="inline-edit" data-field="billing_contact_email_nickname">{{ $client->billing_contact_email_nickname }}</td>
                        <td class="inline-edit" data-field="billing_contact_email">{{ $client->billing_contact_email }}</td>
                        <td class="inline-edit" data-field="billing_contact_phone_office">{{ $client->billing_contact_phone_office }}</td>
                        <td class="inline-edit" data-field="billing_contact_phone_phone">{{ $client->billing_contact_phone_phone }}</td>

                        <td>{{ $client->added_timestamp }}</td>

                        {{-- address --}}
                        <td class="inline-edit" data-field="address_line1">{{ $client->address_line1 }}</td>
                        <td class="inline-edit" data-field="address_line2">{{ $client->address_line2 }}</td>
                        <td class="inline-edit" data-field="address_city">{{ $client->address_city }}</td>
                        <td class="inline-edit" data-field="address_state">{{ $client->address_state }}</td>
                        <td class="inline-edit" data-field="address_zipcode">{{ $client->address_zipcode }}</td>
                        <td class="inline-edit" data-field="address_country">{{ $client->address_country }}</td>

                        {{-- preferences --}}
                        <td class="inline-edit" data-field="contact_preference">{{ $client->contact_preference }}</td>
                        <td class="inline-edit" data-field="billing_preference">{{ $client->billing_preference }}</td>

                        {{-- type/innline --}}
                        <td class="inline-edit" data-field="type">{{ $client->type }}</td>
                        <td class="inline-edit" data-field="innline_types">{{ $client->innline_types }}</td>
                        <td class="inline-edit" data-field="innline_amenities">{{ $client->innline_amenities }}</td>

                        {{-- open / active --}}
                        <td class="inline-edit" data-field="open">{{ $client->open ? 'Yes' : 'No' }}</td>
                        <td class="inline-edit" data-field="active">{{ $client->active ? 'Yes' : 'No' }}</td>

                        {{-- relations --}}
                        {{-- <td class="inline-edit" data-field="primary_contact_id">{{ optional($client->primaryContact)->name ?? $client->primary_contact_id }}</td>
                        <td class="inline-edit" data-field="primary_ad_rep_id">{{ optional($client->primaryAdRep)->name ?? $client->primary_ad_rep_id }}</td>
                        <td class="inline-edit" data-field="secondary_ad_rep_id">{{ optional($client->secondaryAdRep)->name ?? $client->secondary_ad_rep_id }}</td> --}}

                        <td class="inline-edit" data-field="primary_contact_id" data-value="{{ $client->primary_contact_id }}">{{ optional($client->primaryContact)->name }}</td>
                        <td class="inline-edit" data-field="primary_ad_rep_id" data-value="{{ $client->primary_ad_rep_id }}">{{ optional($client->primaryAdRep)->name }}</td>
                        <td class="inline-edit" data-field="secondary_ad_rep_id" data-value="{{ $client->secondary_ad_rep_id }}">{{ optional($client->secondaryAdRep)->name }}</td>
                        {{-- website --}}
                        <td class="inline-edit" data-field="website_part">{{ $client->website_part }}</td>
                        <td class="inline-edit" data-field="website">{{ $client->website }}</td>

                        <td class="inline-edit" data-field="wisconsin_resale_number">{{ $client->wisconsin_resale_number }}</td>

                        <td class="inline-edit" data-field="owner_alumni_school_district">{{ $client->owner_alumni_school_district }}</td>

                        <td class="inline-edit" data-field="newsletter_weekly_business_updates">{{ $client->newsletter_weekly_business_updates ? 'Yes' : 'No' }}</td>
                        <td class="inline-edit" data-field="newsletter_pulse_picks">{{ $client->newsletter_pulse_picks ? 'Yes' : 'No' }}</td>

                        <td class="inline-edit" data-field="marketing_preferences">{{ $client->marketing_preferences }}</td>

                        <td class="inline-edit" data-field="marketing_contact_name">{{ $client->marketing_contact_name }}</td>
                        <td class="inline-edit" data-field="marketing_contact_email_nickname">{{ $client->marketing_contact_email_nickname }}</td>
                        <td class="inline-edit" data-field="marketing_contact_email">{{ $client->marketing_contact_email }}</td>
                        <td class="inline-edit" data-field="marketing_contact_phone_office">{{ $client->marketing_contact_phone_office }}</td>
                        <td class="inline-edit" data-field="marketing_contact_phone_mobile">{{ $client->marketing_contact_phone_mobile }}</td>
                        <td class="inline-edit" data-field="marketing_contact_preference">{{ $client->marketing_contact_preference }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="clientModalTitle">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="clientModalBody">Loading...</div>

        </div>
    </div>
</div>

@endsection

{{-- INLINE EDIT CONFIG --}}
<script>
window.inlineConfig = {
    updateUrl: "/clients/",
    fields: {
        name: "text",
        phone: "text",
        contact_name: "text",
        contact_email_nickname: "text",
        contact_email: "text",
        contact_phone_office: "text",
        contact_phone_mobile: "text",
        billing_contact_name: "text",
        billing_contact_email_nickname: "text",
        billing_contact_email: "text",
        billing_contact_phone_office: "text",
        billing_contact_phone_phone: "text",
        address_line1: "textarea",
        address_line2: "textarea",
        address_city: "text",
        address_state: "select",
        address_zipcode: "text",
        address_country: "text",
        contact_preference: "select",
        billing_preference: "select",
        type: "text",
        innline_types: "text",
        innline_amenities: "text",
        open: "boolean",
        active: "boolean",
        primary_contact_id: "select",
        primary_ad_rep_id: "select",
        secondary_ad_rep_id: "select",
        website_part: "text",
        website: "text",
        wisconsin_resale_number: "text",
        owner_alumni_school_district: "select",
        newsletter_weekly_business_updates: "boolean",
        newsletter_pulse_picks: "boolean",
        marketing_preferences: "select",
        marketing_contact_name: "text",
        marketing_contact_email_nickname: "text",
        marketing_contact_email: "text",
        marketing_contact_phone_office: "text",
        marketing_contact_phone_mobile: "text",
        marketing_contact_preference: "select"
    },
    options: {
        address_state:{
            Alabama: 'Alabama', Alaska: 'Alaska', Arizona: 'Arizona', Arkansas: 'Arkansas', California: 'California', Colorado: 'Colorado', Connecticut: 'Connecticut',
            Delaware: 'Delaware', Florida: 'Florida', Georgia: 'Georgia', Hawaii: 'Hawaii', Idaho: 'Idaho', Illinois: 'Illinois', Indiana: 'Indiana', Iowa: 'Iowa',
            Kansas: 'Kansas', Kentucky: 'Kentucky', Louisiana: 'Louisiana', Maine: 'Maine', Maryland: 'Maryland', Massachusetts: 'Massachusetts', Michigan: 'Michigan',
            Minnesota: 'Minnesota', Mississippi: 'Mississippi', Missouri: 'Missouri', Montana: 'Montana', Nebraska: 'Nebraska', Nevada: 'Nevada',
             Hampshire: 'New Hampshire',  Jersey: 'New Jersey',  Mexico: 'New Mexico',  York: 'New York',  Carolina: 'North Carolina',
             Dakota: 'North Dakota', Ohio:'Ohio' , Oklahoma:'Oklahoma' , Oregon:'Oregon' , Pennsylvania:'Pennsylvania' ,  Island:'Rhode Island',
             Carolina: 'South Carolina',  Dakota: 'South Dakota', Tennessee: 'Tennessee', Texas: 'Texas', Utah: 'Utah', Vermont: 'Vermont',
            Virginia: 'Virginia', Washington: 'Washington',  Virginia: 'West Virginia', Wisconsin: 'Wisconsin', Wyoming: 'Wyoming'
        },
        contact_preference: {
            email: 'Email', phone: 'Phone', text: 'Text', no_preference: 'No Preference', see_notes: 'See Notes'
        },
        billing_preference: { email: 'Email', mail: 'Mail' },
        owner_alumni_school_district: {
            gibraltar:'Gibraltar', sevastopol:'Sevastopol', southern_door:'Southern Door', sturgeon_bay:'Sturgeon Bay',
            washington_island:'Washington Island', other:'Other', non_dc:'Non DC'
        },
        marketing_preferences: { all_marketing: 'All Marketing', no_marketing: 'No Marketing' },
        marketing_contact_preference: { email:'Email', phone:'Phone', text:'Text', no_preference:'No Preference', see_notes:'See Notes' }

    }
};
</script>
<script>
Object.keys(window.inlineConfig.options).forEach(function (key) {
    let opt = window.inlineConfig.options[key];
    if (!Array.isArray(opt)) {
        window.inlineConfig.options[key] = Object.entries(opt).map(([id, label]) => ({
            id: id,
            label: label
        }));
    }
});

// Add options for relation fields
window.inlineConfig.options.primary_contact_id = @json($contacts ?? []).map(item => ({
    id: item.id,
    label: item.name
}));
window.inlineConfig.options.primary_ad_rep_id = @json($adReps ?? []).map(item => ({
    id: item.id,
    label: item.name
}));
window.inlineConfig.options.secondary_ad_rep_id = @json($adReps ?? []).map(item => ({
    id: item.id,
    label: item.name
}));
</script>

@push('scripts')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
@endpush
{{-- @push('scripts')
<script src="{{ asset('js/clients-index.js') }}"></script>
@endpush --}}
