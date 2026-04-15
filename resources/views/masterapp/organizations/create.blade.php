@extends('masterapp.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Create Organization</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card organization-form-card">
            <div class="card-body">
                <form id="organizationForm" action="{{ route('masterapp.organizations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="description_editor" class="form-control" rows="8" placeholder="Write organization description...">{{ old('description') }}</textarea>
                        <small class="form-text text-muted">Use the toolbar to format text. Click <strong>Code View</strong> to edit HTML directly.</small>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h5 class="mb-3">General Info</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="list-group" id="organization-section-nav">
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn active" data-target="section-company">Company</button>
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn" data-target="section-location">Address & Location</button>
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn" data-target="section-contact">Contact Information</button>
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn" data-target="section-communication">Communication Channels</button>
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn" data-target="section-social">Social Media</button>
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn" data-target="section-marketing-preferences">Marketing Preferences</button>
                                    <button type="button" class="list-group-item list-group-item-action org-section-btn" data-target="section-legal-extra">Legal / Extra Info</button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div id="section-company" class="org-section-panel">
                                <div class="border rounded p-3 mb-3">
                                    <h6 class="mb-3">Company Info</h6>

                                    <div class="form-group">
                                        <label>Year Founded</label>
                                        <input type="text" name="year_founded" value="{{ old('year_founded') }}" class="form-control" maxlength="4" placeholder="e.g. 1998">
                                    </div>

                                    <div class="form-group">
                                        <label>Hours</label>
                                        <textarea name="hours" class="form-control" rows="2" placeholder="Free text hours">{{ old('hours') }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Logo</label>
                                        <div class="custom-file">
                                            <input type="file" name="logo" id="logo" class="custom-file-input" accept="image/jpeg,image/png,image/gif,image/webp">
                                            <label class="custom-file-label" for="logo">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Allowed: JPEG, PNG, GIF, WebP. Max 2MB.</small>
                                        <div id="logo-preview-wrap" class="mt-2"></div>
                                    </div>

                                    <div class="form-group">
                                        <label>Seasons Open</label>
                                        @php
                                            $selectedSeasons = old('seasons_open', []);
                                            $selectedSeasons = array_map('intval', (array) $selectedSeasons);
                                        @endphp
                                        <div class="pl-0">
                                            @foreach ($seasons as $season)
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" name="seasons_open[]" id="season_{{ $season->id }}" value="{{ $season->id }}" @checked(in_array((int) $season->id, $selectedSeasons, true))>
                                                    <label class="custom-control-label font-weight-normal" for="season_{{ $season->id }}">{{ $season->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                                </div>

                                <div id="section-location" class="org-section-panel d-none">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-3">Address & Location</h6>
                                        <input type="hidden" name="physical_location_id" id="physical_location_id" value="{{ old('physical_location_id', old('location_id')) }}">
                                        <input type="hidden" name="mailing_location_id" id="mailing_location_id" value="{{ old('mailing_location_id') }}">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group position-relative">
                                                    <label>Physical Address</label>
                                                    <input type="text"
                                                           id="physical_address_search"
                                                           name="address_line1"
                                                           value="{{ old('address_line1') }}"
                                                           class="form-control"
                                                           autocomplete="off"
                                                           placeholder="Type physical address to search location">
                                                    <div id="physical_location_search_loading"
                                                         class="position-absolute d-none"
                                                         style="right: 10px; top: 38px; z-index: 1060;">
                                                        <span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span>
                                                    </div>
                                                    <div id="physical_location_suggestions"
                                                         class="list-group position-absolute w-100 d-none"
                                                         style="z-index: 1050; max-height: 220px; overflow-y: auto;"></div>
                                                </div>
                                                <div id="selected_physical_location_detail" class="alert alert-light border d-none py-2 px-3 mb-3 position-relative">
                                                    <button type="button" class="btn btn-link p-0 position-absolute text-secondary js-clear-physical-location" style="top: 8px; right: 8px; font-size: 1.25rem; line-height: 1;" title="Remove location" aria-label="Remove location">&times;</button>
                                                    <div><strong>Physical Location:</strong> <span id="selected_physical_location_name"></span></div>
                                                    <div class="small text-muted mt-1" id="selected_physical_location_full_address"></div>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="mailing_same_as_physical" name="mailing_same_as_physical" value="1" @checked(old('mailing_same_as_physical'))>
                                                        <label class="custom-control-label font-weight-normal" for="mailing_same_as_physical">Mailing address same as physical address</label>
                                                    </div>
                                                </div>
                                                <div class="form-group position-relative mb-0">
                                                    <label>Mailing Address</label>
                                                    <input type="text"
                                                           id="mailing_address_search"
                                                           name="address_line2"
                                                           value="{{ old('address_line2') }}"
                                                           class="form-control"
                                                           autocomplete="off"
                                                           placeholder="Type mailing address to search location">
                                                    <div id="mailing_location_search_loading"
                                                         class="position-absolute d-none"
                                                         style="right: 10px; top: 38px; z-index: 1060;">
                                                        <span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span>
                                                    </div>
                                                    <div id="mailing_location_suggestions"
                                                         class="list-group position-absolute w-100 d-none"
                                                         style="z-index: 1050; max-height: 220px; overflow-y: auto;"></div>
                                                </div>
                                                <div id="selected_mailing_location_detail" class="alert alert-light border d-none py-2 px-3 mt-3 mb-0 position-relative">
                                                    <button type="button" class="btn btn-link p-0 position-absolute text-secondary js-clear-mailing-location" style="top: 8px; right: 8px; font-size: 1.25rem; line-height: 1;" title="Remove location" aria-label="Remove location">&times;</button>
                                                    <div><strong>Mailing Location:</strong> <span id="selected_mailing_location_name"></span></div>
                                                    <div class="small text-muted mt-1" id="selected_mailing_location_full_address"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="section-contact" class="org-section-panel d-none">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-3">Contact Information</h6>
                                        <div class="form-group">
                                            <label>Search contact</label>
                                            <div class="position-relative">
                                                <input type="text"
                                                       id="contact_search"
                                                       class="form-control"
                                                       autocomplete="off"
                                                       placeholder="Type name or any contact detail to search">
                                                <div id="contact_search_loading"
                                                     class="position-absolute d-none"
                                                     style="right: 10px; top: 8px; z-index: 1060;">
                                                    <span class="spinner-border spinner-border-sm text-secondary" role="status" aria-hidden="true"></span>
                                                </div>
                                                <div id="contact_search_suggestions"
                                                     class="list-group position-absolute w-100 d-none"
                                                     style="z-index: 1050; max-height: 220px; overflow-y: auto;"></div>
                                            </div>
                                        </div>
                                        <div id="selected_contacts_list" class="mt-3"></div>
                                    </div>
                                </div>

                                <div id="section-communication" class="org-section-panel d-none">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-3">Communication Channels</h6>
                                        <div class="form-group">
                                            <label>Phone Number</label>
                                            <input type="text" name="phone" id="org_phone" value="{{ old('phone') }}" class="form-control js-us-phone" placeholder="123-456-7890" maxlength="12" inputmode="numeric" autocomplete="tel">
                                        </div>
                                        <div class="form-group">
                                            <label>Fax Number <span class="text-muted">(optional)</span></label>
                                            <input type="text" name="fax" id="org_fax" value="{{ old('fax') }}" class="form-control js-us-phone" placeholder="123-456-7890" maxlength="12" inputmode="numeric" autocomplete="tel">
                                        </div>
                                        <div class="form-group">
                                            <label>Email (General)</label>
                                            <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-control" placeholder="General email">
                                        </div>
                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="text" name="website" value="{{ old('website') }}" class="form-control" placeholder="Website URL">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Website (PULSE)</label>
                                            <input type="text" name="website_part" value="{{ old('website_part') }}" class="form-control" placeholder="Website (PULSE) URL">
                                        </div>
                                    </div>
                                </div>

                                <div id="section-social" class="org-section-panel d-none">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-3">Social Media</h6>
                                        <div class="form-group">
                                            <label>Facebook</label>
                                            <input type="text" name="facebook" value="{{ old('facebook') }}" class="form-control" placeholder="Facebook URL or handle">
                                        </div>
                                        <div class="form-group">
                                            <label>Twitter</label>
                                            <input type="text" name="twitter" value="{{ old('twitter') }}" class="form-control" placeholder="Twitter URL or handle">
                                        </div>
                                        <div class="form-group">
                                            <label>Instagram</label>
                                            <input type="text" name="instagram" value="{{ old('instagram') }}" class="form-control" placeholder="Instagram URL or handle">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Pinterest</label>
                                            <input type="text" name="pinterest" value="{{ old('pinterest') }}" class="form-control" placeholder="Pinterest URL or handle">
                                        </div>
                                    </div>
                                </div>

                                <div id="section-marketing-preferences" class="org-section-panel d-none">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-3">Marketing Preferences</h6>
                                        <div class="form-group">
                                            <label>Marketing Preferences <span class="text-muted">(Optional)</span></label>
                                            <select name="marketing_preferences" class="form-control">
                                                <option value="">—</option>
                                                <option value="all_marketing" @selected(old('marketing_preferences') === 'all_marketing')>Yes</option>
                                                <option value="no_marketing" @selected(old('marketing_preferences') === 'no_marketing')>No</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Newsletter Weekly Business Updates <span class="text-muted">(Optional)</span></label>
                                            <select name="newsletter_weekly_business_updates" class="form-control">
                                                <option value="">—</option>
                                                <option value="1" @selected((string) old('newsletter_weekly_business_updates') === '1')>Yes</option>
                                                <option value="0" @selected((string) old('newsletter_weekly_business_updates') === '0')>No</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Newsletter Pulse Picks <span class="text-muted">(Optional)</span></label>
                                            <select name="newsletter_pulse_picks" class="form-control">
                                                <option value="">—</option>
                                                <option value="1" @selected((string) old('newsletter_pulse_picks') === '1')>Yes</option>
                                                <option value="0" @selected((string) old('newsletter_pulse_picks') === '0')>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="section-legal-extra" class="org-section-panel d-none">
                                    <div class="border rounded p-3">
                                        <h6 class="mb-3">Legal / Extra Info</h6>
                                        <div class="form-group">
                                            <label>WI Resale # <span class="text-muted">(Optional)</span></label>
                                            <input type="text" name="wisconsin_resale_number" value="{{ old('wisconsin_resale_number') }}" class="form-control" placeholder="Wisconsin resale number">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Alumni School District <span class="text-muted">(Optional)</span></label>
                                            <input type="text" name="owner_alumni_school_district" value="{{ old('owner_alumni_school_district') }}" class="form-control" placeholder="e.g. Gibraltar, Sevastopol, Sturgeon Bay">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h5 class="mb-3">More Details</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="list-group" id="types-parent-nav">
                                    @foreach ($parentClientTypes as $pt)
                                        @php $parentEnabled = in_array((int) $pt->id, $selectedClientTypeIds); @endphp
                                        <button type="button"
                                                class="list-group-item list-group-item-action types-parent-btn font-weight-normal text-left d-flex align-items-center"
                                                data-id="{{ $pt->id }}"
                                                data-has-children="{{ $pt->children->isNotEmpty() ? '1' : '0' }}">
                                            <i class="fa fa-check types-parent-tick mr-1 flex-shrink-0 {{ $parentEnabled ? 'text-success' : 'text-muted' }}" aria-hidden="true"></i>
                                            <span>{{ $pt->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div id="types_right_panel">
                                    @foreach ($parentClientTypes as $pt)
                                        @php
                                            $typeChildren = $pt->children;
                                            $typeChildCount = $typeChildren->count();
                                        @endphp
                                        <div id="types_panel_{{ $pt->id }}" class="types-panel d-none" data-parent-id="{{ $pt->id }}">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" class="custom-control-input js-enable-parent-type" name="client_type_ids[]" id="enable_type_{{ $pt->id }}" value="{{ $pt->id }}" data-parent-id="{{ $pt->id }}" {{ in_array((int) $pt->id, $selectedClientTypeIds) ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold" for="enable_type_{{ $pt->id }}">Enable "{{ $pt->name }}"</label>
                                            </div>
                                            @if($typeChildCount > 0)
                                                <label class="mb-2 d-block font-weight-normal">Type</label>
                                                <div class="form-group mb-0 pl-0 {{ $typeChildCount > 20 ? 'types-list-scroll' : '' }}" @if($typeChildCount > 20) style="max-height: 320px; overflow-y: auto; overflow-x: hidden;" @endif>
                                                    @if($typeChildCount > 10)
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                @foreach($typeChildren->take((int) ceil($typeChildCount / 2)) as $child)
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" name="client_type_ids[]" id="type_{{ $pt->id }}_{{ $child->id }}" value="{{ $child->id }}">
                                                                        <label class="custom-control-label font-weight-normal" for="type_{{ $pt->id }}_{{ $child->id }}">{{ $child->name }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div class="col-md-6">
                                                                @foreach($typeChildren->slice((int) ceil($typeChildCount / 2)) as $child)
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" name="client_type_ids[]" id="type_{{ $pt->id }}_{{ $child->id }}" value="{{ $child->id }}">
                                                                        <label class="custom-control-label font-weight-normal" for="type_{{ $pt->id }}_{{ $child->id }}">{{ $child->name }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="pl-0">
                                                            @foreach($typeChildren as $child)
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="client_type_ids[]" id="type_{{ $pt->id }}_{{ $child->id }}" value="{{ $child->id }}">
                                                                    <label class="custom-control-label font-weight-normal" for="type_{{ $pt->id }}_{{ $child->id }}">{{ $child->name }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                           
                                            @endif
                                            @if(strtolower($pt->name) === 'restaurants' && isset($restaurantPriceRanges))
                                                <label class="mb-2 d-block font-weight-bold mt-3">Price Range</label>
                                                <div class="form-group mb-0 pl-0">
                                                    @foreach($restaurantPriceRanges as $priceRange)
                                                        <div class="custom-control custom-radio">
                                                            <input type="radio" class="custom-control-input" name="restaurant_price_range_id" id="restaurant_price_range_id_{{ $priceRange->id }}" value="{{ $priceRange->id }}" @checked((string) old('restaurant_price_range_id') === (string) $priceRange->id)>
                                                            <label class="custom-control-label font-weight-normal" for="restaurant_price_range_id_{{ $priceRange->id }}">{{ $priceRange->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(strtolower($pt->name) === 'restaurants' && isset($restaurantMeals) && $restaurantMeals->isNotEmpty())
                                                <label class="mb-2 d-block font-weight-bold mt-3">Meals</label>
                                                <div class="form-group mb-0 pl-0">
                                                    @foreach($restaurantMeals as $meal)
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" name="restaurant_meal_ids[]" id="restaurant_meal_id_{{ $meal->id }}" value="{{ $meal->id }}" @checked(in_array($meal->id, (array) old('restaurant_meal_ids', []), true))>
                                                            <label class="custom-control-label font-weight-normal" for="restaurant_meal_id_{{ $meal->id }}">{{ $meal->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if($pt->amenities->isNotEmpty())
                                                @php
                                                    $typeAmenities = $pt->amenities;
                                                    $amenityCount = $typeAmenities->count();
                                                @endphp
                                                <label class="mb-2 d-block font-weight-bold mt-3">Amenities</label>
                                                <div class="form-group mb-0 pl-0 {{ $amenityCount > 20 ? 'types-list-scroll' : '' }}" @if($amenityCount > 20) style="max-height: 320px; overflow-y: auto; overflow-x: hidden;" @endif>
                                                    @if($amenityCount > 10)
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                @foreach($typeAmenities->take((int) ceil($amenityCount / 2)) as $amenity)
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" name="amenity_ids[]" id="amenity_{{ $pt->id }}_{{ $amenity->id }}" value="{{ $amenity->id }}">
                                                                        <label class="custom-control-label font-weight-normal" for="amenity_{{ $pt->id }}_{{ $amenity->id }}">{{ $amenity->name }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div class="col-md-6">
                                                                @foreach($typeAmenities->slice((int) ceil($amenityCount / 2)) as $amenity)
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" name="amenity_ids[]" id="amenity_{{ $pt->id }}_{{ $amenity->id }}" value="{{ $amenity->id }}">
                                                                        <label class="custom-control-label font-weight-normal" for="amenity_{{ $pt->id }}_{{ $amenity->id }}">{{ $amenity->name }}</label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="pl-0">
                                                            @foreach($typeAmenities as $amenity)
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="amenity_ids[]" id="amenity_{{ $pt->id }}_{{ $amenity->id }}" value="{{ $amenity->id }}">
                                                                    <label class="custom-control-label font-weight-normal" for="amenity_{{ $pt->id }}_{{ $amenity->id }}">{{ $amenity->name }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Open</label>
                                <select name="open" class="form-control">
                                    <option value="1" @selected(old('open', '1') === '1')>Yes</option>
                                    <option value="0" @selected(old('open') === '0')>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Active</label>
                                <select name="active" class="form-control">
                                    <option value="1" @selected(old('active', '1') === '1')>Active</option>
                                    <option value="0" @selected(old('active') === '0')>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Excerpts</label>
                        <textarea name="excerpts" class="form-control" rows="3" placeholder="Write a short excerpt">{{ old('excerpts') }}</textarea>
                    </div>

                    <a href="{{ route('masterapp.organizations.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="btn-create-organization">
                        <span id="btn-create-text">Create</span>
                        <span id="btn-create-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/summernote/summernote.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/summernote/summernote.min.js') }}"></script>
@php
    $organizationFormConfig = [
        'suggestUrl' => route('masterapp.organizations.locations.suggest'),
        'contactSuggestUrl' => route('masterapp.organizations.contacts.suggest'),
        'mode' => 'create',
        'redirectUrl' => route('masterapp.organizations.index'),
    ];
@endphp
<script type="application/json" id="organization-form-config">{!! json_encode($organizationFormConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/organization.js') }}"></script>
@endpush
