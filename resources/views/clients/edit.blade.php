<div class="container">
    {{-- <div class="card">
        <div class="card-body"> --}}

            <form id="client-edit-form" method="POST" action="{{ route('clients.update', $client) }}">
                @csrf
                @method('PUT')
                    {{-- BASIC INFO --}}
                <div class="row mb-3">
                 <label class="col-sm-3 col-form-label fw-semibold">Client Name <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $client->name) }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Phone</label>
                    <div class="col-sm-9">
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $client->phone) }}">
                    </div>
                </div>

                    {{-- CONTACT INFORMATION --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Contact Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $client->contact_name) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Contact Email Nickname</label>
                    <div class="col-sm-9">
                        <input type="text" name="contact_email_nickname" class="form-control"  value="{{ old('contact_email_nickname', $client->contact_email_nickname) }}">
                    </div>
                </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Contact Email</label>
                    <div class="col-sm-9">
                        <input type="email" name="contact_email" class="form-control"  value="{{ old('contact_email', $client->contact_email) }}">
                    </div>
                </div>

                <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-semibold">Contact Phone Office</label>
                    <div class="col-sm-9">
                        <input type="text" name="contact_phone_office" class="form-control"  value="{{ old('contact_phone_office', $client->contact_phone_office) }}">
                    </div>
                </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Contact Phone Mobile</label>
                        <div class="col-sm-9">
                        <input type="text" name="contact_phone_mobile" class="form-control"  value="{{ old('contact_phone_mobile', $client->contact_phone_mobile) }}">
                    </div>
                </div>

                {{-- CONTACT PREFERENCE ENUM --}}
                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Contact Preference</label>
                    <div class="col-sm-9">
                        <select name="contact_preference" class="form-control">
                            @foreach(['email','phone','text','no_preference','see_notes'] as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('contact_preference', $client->contact_preference) == $opt ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_',' ', $opt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    {{-- BILLING CONTACT --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Billing Contact Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="billing_contact_name" class="form-control"
                               value="{{ old('billing_contact_name', $client->billing_contact_name) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Billing Email Nickname</label>
                    <div class="col-sm-9">
                        <input type="text" name="billing_contact_email_nickname" class="form-control"
                               value="{{ old('billing_contact_email_nickname', $client->billing_contact_email_nickname) }}">
                    </div>
                </div>
                <div class="row mb-3">
                   <label class="col-sm-3 col-form-label fw-semibold">Billing Email</label>
                    <div class="col-sm-9">
                        <input type="email" name="billing_contact_email" class="form-control"
                               value="{{ old('billing_contact_email', $client->billing_contact_email) }}">
                    </div>
                </div>

                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Billing Phone Office</label>
                    <div class="col-sm-9">
                        <input type="text" name="billing_contact_phone_office" class="form-control"
                               value="{{ old('billing_contact_phone_office', $client->billing_contact_phone_office) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Billing Phone Mobile</label>
                    <div class="col-sm-9">
                        <input type="text" name="billing_contact_phone_phone" class="form-control"
                               value="{{ old('billing_contact_phone_phone', $client->billing_contact_phone_phone) }}">
                    </div>
                </div>

                {{-- Billing Preference ENUM --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Billing Preference</label>
                    <div class="col-sm-9">
                        <select name="billing_preference" class="form-control">
                            @foreach(['email','mail'] as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('billing_preference', $client->billing_preference) == $opt ? 'selected' : '' }}>
                                    {{ ucfirst($opt) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    {{-- ADDRESS DETAILS --}}
                <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-semibold">Address Line 1</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_line1" class="form-control"
                               value="{{ old('address_line1', $client->address_line1) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Address Line 2</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_line2" class="form-control"
                               value="{{ old('address_line2', $client->address_line2) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">City</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_city" class="form-control"
                               value="{{ old('address_city', $client->address_city) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">State</label>
                    <div class="col-sm-9">
                        {{-- <input type="text" name="address_state" class="form-control"
                               value="{{ old('address_state', $client->address_state) }}"> --}}
                        <select name="address_state" class="form-control">
                            <option value="">-- select state --</option>    
                            @foreach($states as $state)
                                <option value="{{ $state }}"
                                    {{ old('address_state', $client->address_state) == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Zipcode</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_zipcode" class="form-control"
                               value="{{ old('address_zipcode', $client->address_zipcode) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Country</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_country" class="form-control"
                               value="{{ old('address_country', $client->address_country) }}">
                    </div>
                </div>

                    {{-- BUSINESS DETAILS --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Type</label>
                    <div class="col-sm-9">
                        <input type="text" name="type" class="form-control"
                               value="{{ old('type', $client->type) }}">
                    </div>
                </div>
                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Innline Types</label>
                    <div class="col-sm-9">
                        <input type="text" name="innline_types" class="form-control"
                               value="{{ old('innline_types', $client->innline_types) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Innline Amenities</label>
                    <div class="col-sm-9">
                        <input type="text" name="innline_amenities" class="form-control"
                               value="{{ old('innline_amenities', $client->innline_amenities) }}">
                    </div>
                </div>

                    {{-- BOOLEANS: open, active --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Open</label>
                    <div class="col-sm-9">
                        <select name="open" class="form-control">
                            <option value="1" {{ old('open', $client->open) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !old('open', $client->open) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
                    <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Active</label>
                    <div class="col-sm-9">
                        <select name="active" class="form-control">
                            <option value="1" {{ old('active', $client->active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('active', $client->active) ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                    {{-- RELATION FIELDS --}}
                    <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Primary Contact</label>
                    <div class="col-sm-9">
                        <select name="primary_contact_id" class="form-control">
                            <option value="">Select</option>
                            @foreach($users ?? [] as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('primary_contact_id', $client->primary_contact_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    </div>
                    <div class="row mb-3">
                   <label class="col-sm-3 col-form-label fw-semibold">Primary Ad Rep</label>
                    <div class="col-sm-9">
                        <select name="primary_ad_rep_id" class="form-control">
                            <option value="">Select</option>
                            @foreach($adReps ?? [] as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ old('primary_ad_rep_id', $client->primary_ad_rep_id) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Secondary Ad Rep</label>
                    <div class="col-sm-9">
                        <select name="secondary_ad_rep_id" class="form-control">
                            <option value="">Select</option>
                            @foreach($adReps ?? [] as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ old('secondary_ad_rep_id', $client->secondary_ad_rep_id) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    {{-- WEBSITE FIELDS --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Website Part</label>
                    <div class="col-sm-9">
                        <input type="text" name="website_part" class="form-control"
                               value="{{ old('website_part', $client->website_part) }}">
                    </div>
                </div>
                    <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Website</label>
                    <div class="col-sm-9">
                        <input type="text" name="website" class="form-control"
                               value="{{ old('website', $client->website) }}">
                    </div>
                </div>

                    {{-- OWNER ALUMNI SCHOOL DISTRICT ENUM --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Owner Alumni School District</label>
                    <div class="col-sm-9">
                        <select name="owner_alumni_school_district" class="form-control">
                            <option value="">Select</option>
                            @foreach(['gibraltar','sevastopol','southern_door','sturgeon_bay','washington_island','other','non_dc'] as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('owner_alumni_school_district', $client->owner_alumni_school_district) == $opt ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $opt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                    {{-- NEWSLETTER CHECKBOXES --}}
                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Weekly Business Updates</label>
                    <div class="col-sm-9">
                        <select name="newsletter_weekly_business_updates" class="form-control">
                            <option value="1" {{ old('newsletter_weekly_business_updates', $client->newsletter_weekly_business_updates) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !old('newsletter_weekly_business_updates', $client->newsletter_weekly_business_updates) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Pulse Picks Newsletter</label>
                    <div class="col-sm-9">
                        <select name="newsletter_pulse_picks" class="form-control">
                            <option value="1" {{ old('newsletter_pulse_picks', $client->newsletter_pulse_picks) ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !old('newsletter_pulse_picks', $client->newsletter_pulse_picks) ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>


                    {{-- MARKETING PREFERENCES ENUM --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Marketing Preferences</label>
                    <div class="col-sm-9">
                        <select name="marketing_preferences" class="form-control">
                            @foreach(['all_marketing','no_marketing'] as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('marketing_preferences', $client->marketing_preferences) == $opt ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_',' ', $opt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    {{-- MARKETING CONTACT --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Marketing Contact Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="marketing_contact_name" class="form-control"
                               value="{{ old('marketing_contact_name', $client->marketing_contact_name) }}">
                    </div>
                </div>
                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Marketing Email Nickname</label>
                    <div class="col-sm-9">
                        <input type="text" name="marketing_contact_email_nickname" class="form-control"
                               value="{{ old('marketing_contact_email_nickname', $client->marketing_contact_email_nickname) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Marketing Email</label>
                    <div class="col-sm-9">
                        <input type="email" name="marketing_contact_email" class="form-control"
                               value="{{ old('marketing_contact_email', $client->marketing_contact_email) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Marketing Phone Office</label>
                    <div class="col-sm-9">
                        <input type="text" name="marketing_contact_phone_office" class="form-control"
                               value="{{ old('marketing_contact_phone_office', $client->marketing_contact_phone_office) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Marketing Phone Mobile</label>
                    <div class="col-sm-9">
                        <input type="text" name="marketing_contact_phone_mobile" class="form-control"
                               value="{{ old('marketing_contact_phone_mobile', $client->marketing_contact_phone_mobile) }}">
                    </div>
                </div>

                {{-- Marketing Contact Preference ENUM --}}
                <div class="row mb-3">
                 <label class="col-sm-3 col-form-label fw-semibold">Marketing Contact Preference</label>
                    <div class="col-sm-9">
                        <select name="marketing_contact_preference" class="form-control">
                            @foreach(['email','phone','text','no_preference','see_notes'] as $opt)
                                <option value="{{ $opt }}"
                                    {{ old('marketing_contact_preference', $client->marketing_contact_preference) == $opt ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_',' ', $opt)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    {{-- SAVE BUTTONS --}}
                <div class="mt-4 text-end">
                    <a href="{{ route('masterapp.contacts.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </form>

        </div>
    {{-- </div>
</div> --}}

@push('scripts')
<script>
    window.CLIENT_ENTRY = @json($client);
</script>
<script src="{{ asset('js/clients-edit.js') }}"></script>
@endpush
