<div class="container-fluid">
    {{-- <div class="card">
        <div class="card-body"> --}}

            <form id="client-create-form" action="{{ route('clients.store') }}" method="POST">
                @csrf

                    {{-- BASIC INFORMATION --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Client Name <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Phone</label>
                    <div class="col-sm-9">
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>

                {{-- <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">Type</label>
                    <div class="col-sm-9">
                        <input type="text" name="type" class="form-control">
                    </div>
                </div> --}}


                    {{-- CONTACT INFORMATION --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Contact Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="contact_name" class="form-control">
                    </div>
                </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Email Nickname</label>
                        <div class="col-sm-9">
                        <input type="text" name="contact_email_nickname" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-9">
                        <input type="email" name="contact_email" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Phone (Office)</label>
                        <div class="col-sm-9">
                        <input type="text" name="contact_phone_office" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Phone (Mobile)</label>
                        <div class="col-sm-9">
                        <input type="text" name="contact_phone_mobile" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-semibold">Contact Preference</label>
                        <div class="col-sm-9">
                        <select name="contact_preference" class="form-select">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="text">Text</option>
                            <option value="no_preference">No Preference</option>
                            <option value="see_notes">See Notes</option>
                        </select>
                    </div>
                </div>

                    {{-- BILLING INFORMATION --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Billing Contact Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="billing_contact_name" class="form-control">
                    </div>
                </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Billing Email Nickname</label>
                        <div class="col-sm-9">
                        <input type="text" name="billing_contact_email_nickname" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Billing Email</label>
                        <div class="col-sm-9">
                        <input type="email" name="billing_contact_email" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Billing Phone (Office)</label>
                        <div class="col-sm-9">
                        <input type="text" name="billing_contact_phone_office" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Billing Phone</label>
                        <div class="col-sm-9">
                        <input type="text" name="billing_contact_phone_phone" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Billing Preference</label>
                        <div class="col-sm-9">
                        <select name="billing_preference" class="form-select">
                            <option value="email">Email</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                </div>

                    {{-- MARKETING INFORMATION --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Marketing Contact</label>
                    <div class="col-sm-9">
                        <input type="text" name="marketing_contact_name" class="form-control">
                    </div>
                </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Marketing Email Nickname</label>
                        <div class="col-sm-9">
                        <input type="text" name="marketing_contact_email_nickname" class="form-control">
                        </div>
                    </div>
                        <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Marketing Email</label>
                        <div class="col-sm-9">
                        <input type="email" name="marketing_contact_email" class="form-control">
                        </div>
                        </div>
                            <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Marketing Phone (Office)</label>
                        <div class="col-sm-9">
                        <input type="text" name="marketing_contact_phone_office" class="form-control">
                        </div>
                            </div>
                                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Marketing Phone (Mobile)</label>
                        <div class="col-sm-9">
                        <input type="text" name="marketing_contact_phone_mobile" class="form-control">
                        </div>
                                </div>
                        <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Marketing Contact Preference</label>
                        <div class="col-sm-9">
                        <select name="marketing_contact_preference" class="form-select">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="text">Text</option>
                            <option value="no_preference">No Preference</option>
                            <option value="see_notes">See Notes</option>
                        </select>
                    </div>
                </div>


                    {{-- ADDRESS --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Address Line 1</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_line1" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Address Line 2</label>
                        <div class="col-sm-9">
                        <input type="text" name="address_line2" class="form-control">
                        </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">City</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_city" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">State</label>
                    <div class="col-sm-9">
                        <select name="address_state" class="form-select">
                            <option value="">-- select state --</option>
                            @foreach($states as $state)
                                <option value="{{ $state}}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Zipcode</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_zipcode" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Country</label>
                    <div class="col-sm-9">
                        <input type="text" name="address_country" class="form-control">
                    </div>
                </div>

                    {{-- OTHER DETAILS --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Type</label>
                    <div class="col-sm-9">
                        <input type="text" name="innline_types" class="form-control">
                </div>
            </div>
            <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Innline Type</label>
                    <div class="col-sm-9">
                        <input type="text" name="innline_types" class="form-control">
                </div>
            </div>
            <div class="row mb-3">                  
                        <label class="col-sm-3 col-form-label">Innline Amenities</label>
                        <div class="col-sm-9">
                        <input type="text" name="innline_amenities" class="form-control">
                        </div>
            </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Wisconsin Resale Number</label>
                        <div class="col-sm-9">
                        <input type="text" name="wisconsin_resale_number" class="form-control">
                    </div>
                </div>


                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Website (PULSE)</label>
                    <div class="col-sm-9">
                        <input type="text" name="website_part" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Website</label>
                        <div class="col-sm-9">
                        <input type="text" name="website" class="form-control">
                        </div>
                </div>
                       <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Marketing Preferences</label>
                        <div class="col-sm-9">
                        <select name="marketing_preferences" class="form-select">
                            <option value="all_marketing">All Marketing</option>
                            <option value="no_marketing">No Marketing</option>
                        </select>
                    </div>
                </div>

                    {{-- ADMIN / RELATION USERS --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Primary Contact</label>
                    <div class="col-sm-9">
                        <select name="primary_contact_id" class="form-select">
                            <option value="">-- choose user --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Primary Ad Rep</label>
                    <div class="col-sm-9">
                        <select name="primary_ad_rep_id" class="form-select">
                            <option value="">-- choose user --</option>
                            @foreach($adReps as $rep)
                                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Secondary Ad Rep</label>
                    <div class="col-sm-9">
                        <select name="secondary_ad_rep_id" class="form-select">
                            <option value="">-- choose user --</option>
                            @foreach($adReps as $rep)
                                <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    {{-- SYSTEM SETTINGS --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Open</label>
                    <div class="col-sm-9">
                        <select name="open" class="form-select">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Active</label>
                    <div class="col-sm-9">
                        <select name="active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                    {{-- ACTION BUTTONS --}}
                <div class="text-end mt-4">
                    <a href="{{ route('masterapp.contacts.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-success btn-sm">Create Client</button>
                </div>

            </form>
        </div>
    {{-- </div>
</div> --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/clients-create.js') }}"></script>
@endpush
