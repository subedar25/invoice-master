@extends('masterapp.layouts.app')

@section('title', 'View Organization')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">View Organization</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end">
                <a href="{{ route('masterapp.organizations.index') }}" class="btn btn-secondary mr-2">Back to List</a>
                @can('edit-organization')
                <a href="{{ route('masterapp.organizations.edit', $organization->id) }}" class="btn btn-primary"><i class="fa fa-edit mr-1"></i> Edit</a>
                @endcan
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card organization-form-card">
            <div class="card-body">
                <div class="form-group mb-2">
                    <p class="mb-0 font-weight-bold">{{ $organization->name ?: '—' }}</p>
                </div>

                <div class="form-group mb-3">
                    <div class="border rounded p-3 bg-light">
                        @if($organization->description)
                            {!! $organization->description !!}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
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
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Year Founded</label>
                                        <p class="mb-0">{{ $organization->year_founded ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Hours</label>
                                        <p class="mb-0">{{ $organization->hours ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Logo</label>
                                        @if($organization->logo)
                                            <p class="mb-1"><img src="{{ asset('clients/' . $organization->logo) }}" alt="Logo" style="max-height: 80px;"></p>
                                        @else
                                            <p class="mb-0 text-muted">—</p>
                                        @endif
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-muted small">Seasons Open</label>
                                        @php
                                            $seasonNames = $seasons->whereIn('id', $selectedSeasonIds)->pluck('name');
                                        @endphp
                                        <p class="mb-0">{{ $seasonNames->isNotEmpty() ? $seasonNames->implode(', ') : '—' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div id="section-location" class="org-section-panel d-none">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Address & Location</h6>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Physical Location</label>
                                        @if($linkedPhysicalLocation)
                                            <p class="mb-0 font-weight-bold">{{ $linkedPhysicalLocation->name }}</p>
                                            <p class="mb-0 small text-muted">{{ implode(', ', array_filter([$linkedPhysicalLocation->address, $linkedPhysicalLocation->city, $linkedPhysicalLocation->state, $linkedPhysicalLocation->postal_code, $linkedPhysicalLocation->country])) }}</p>
                                        @else
                                            <p class="mb-0 text-muted">—</p>
                                        @endif
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Mailing address same as physical address</label>
                                        <p class="mb-0">{{ ($linkedPhysicalLocation && $linkedMailingLocation && (int) $linkedPhysicalLocation->id === (int) $linkedMailingLocation->id) ? 'Yes' : 'No' }}</p>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-muted small">Mailing Location</label>
                                        @if($linkedMailingLocation)
                                            <p class="mb-0 font-weight-bold">{{ $linkedMailingLocation->name }}</p>
                                            <p class="mb-0 small text-muted">{{ implode(', ', array_filter([$linkedMailingLocation->address, $linkedMailingLocation->city, $linkedMailingLocation->state, $linkedMailingLocation->postal_code, $linkedMailingLocation->country])) }}</p>
                                        @else
                                            <p class="mb-0 text-muted">—</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div id="section-contact" class="org-section-panel d-none">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Contact Information</h6>
                                    @if(count($linkedContacts) > 0)
                                        @foreach($linkedContacts as $c)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <p class="mb-1 font-weight-bold">{{ $c['contact_name'] ?? '' }}</p>
                                                <p class="mb-0 small text-muted">
                                                    @if(!empty($c['items']))
                                                        @php
                                                            $parts = [];
                                                            foreach ($c['items'] as $i) {
                                                                $typeLabel = ucfirst($i['type'] ?? '');
                                                                if (isset($i['value']) && $i['value'] !== '') {
                                                                    $parts[] = $typeLabel . ': ' . $i['value'];
                                                                }
                                                            }
                                                        @endphp
                                                        {{ implode(' · ', $parts) }}
                                                    @else
                                                        —
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="mb-0 text-muted">—</p>
                                    @endif
                                </div>
                            </div>

                            <div id="section-communication" class="org-section-panel d-none">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Communication Channels</h6>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Phone Number</label>
                                        <p class="mb-0">{{ $organization->phone ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Fax Number</label>
                                        <p class="mb-0">{{ $organization->fax ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Email (General)</label>
                                        <p class="mb-0">{{ $organization->contact_email ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Website</label>
                                        <p class="mb-0">{{ $organization->website ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-muted small">Website (PULSE)</label>
                                        <p class="mb-0">{{ $organization->website_part ?: '—' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div id="section-social" class="org-section-panel d-none">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Social Media</h6>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Facebook</label>
                                        <p class="mb-0">{{ $organization->facebook ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Twitter</label>
                                        <p class="mb-0">{{ $organization->twitter ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Instagram</label>
                                        <p class="mb-0">{{ $organization->instagram ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-muted small">Pinterest</label>
                                        <p class="mb-0">{{ $organization->pinterest ?: '—' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div id="section-marketing-preferences" class="org-section-panel d-none">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Marketing Preferences</h6>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Marketing Preferences</label>
                                        <p class="mb-0">{{ $organization->marketing_preferences === 'all_marketing' ? 'Yes' : ($organization->marketing_preferences === 'no_marketing' ? 'No' : '—') }}</p>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">Newsletter Weekly Business Updates</label>
                                        <p class="mb-0">{{ (string) $organization->newsletter_weekly_business_updates === '1' ? 'Yes' : ((string) $organization->newsletter_weekly_business_updates === '0' ? 'No' : '—') }}</p>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-muted small">Newsletter Pulse Picks</label>
                                        <p class="mb-0">{{ (string) $organization->newsletter_pulse_picks === '1' ? 'Yes' : ((string) $organization->newsletter_pulse_picks === '0' ? 'No' : '—') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div id="section-legal-extra" class="org-section-panel d-none">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3">Legal / Extra Info</h6>
                                    <div class="form-group mb-2">
                                        <label class="text-muted small">WI Resale #</label>
                                        <p class="mb-0">{{ $organization->wisconsin_resale_number ?: '—' }}</p>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-muted small">Alumni School District</label>
                                        <p class="mb-0">{{ $organization->owner_alumni_school_district ?: '—' }}</p>
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
                                    @php
                                        $parentEnabled = in_array((int) $pt->id, $selectedClientTypeIds);
                                        $hasEnabledChild = $pt->children->whereIn('id', $selectedClientTypeIds)->isNotEmpty();
                                        $isEnabled = $parentEnabled || $hasEnabledChild;
                                    @endphp
                                    @if($isEnabled)
                                    <button type="button"
                                            class="list-group-item list-group-item-action types-parent-btn font-weight-normal text-left d-flex align-items-center"
                                            data-id="{{ $pt->id }}"
                                            data-has-children="{{ $pt->children->isNotEmpty() ? '1' : '0' }}">
                                        <i class="fa fa-check types-parent-tick mr-1 flex-shrink-0 text-success" aria-hidden="true"></i>
                                        <span>{{ $pt->name }}</span>
                                    </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div id="types_right_panel">
                                @foreach ($parentClientTypes as $pt)
                                    @php
                                        $parentEnabled = in_array((int) $pt->id, $selectedClientTypeIds);
                                        $hasEnabledChild = $pt->children->whereIn('id', $selectedClientTypeIds)->isNotEmpty();
                                        $isEnabled = $parentEnabled || $hasEnabledChild;
                                        $typeChildren = $pt->children;
                                        $typeChildCount = $typeChildren->count();
                                    @endphp
                                    @if($isEnabled)
                                    <div id="types_panel_{{ $pt->id }}" class="types-panel d-none" data-parent-id="{{ $pt->id }}">
                                        <h6 class="mb-3 font-weight-bold">{{ $pt->name }}</h6>
                                        @if($typeChildCount > 0)
                                            <div class="form-group mb-2">
                                                <label class="text-muted small">Type</label>
                                                @php
                                                    $selectedChildNames = $typeChildren->whereIn('id', $selectedClientTypeIds)->pluck('name');
                                                @endphp
                                                @if($selectedChildNames->isNotEmpty())
                                                    <ul class="list-unstyled mb-0 pl-0">
                                                        @foreach($selectedChildNames as $name)
                                                            <li class="mb-1 d-flex align-items-center"><span class="list-bullet-circle mr-2"></span>{{ $name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mb-0 text-muted">—</p>
                                                @endif
                                            </div>
                                        @endif
                                        @if(strtolower($pt->name) === 'restaurants' && isset($restaurantPriceRanges))
                                            <div class="form-group mb-2">
                                                <label class="text-muted small font-weight-bold">Price Range</label>
                                                @php
                                                    $priceRange = $restaurantPriceRanges->firstWhere('id', $organization->restaurant_price_range_id);
                                                @endphp
                                                <p class="mb-0">{{ $priceRange ? $priceRange->name : '—' }}</p>
                                            </div>
                                        @endif
                                        @if(strtolower($pt->name) === 'restaurants' && isset($restaurantMeals) && $restaurantMeals->isNotEmpty())
                                            <div class="form-group mb-2">
                                                <label class="text-muted small font-weight-bold">Meals</label>
                                                @php
                                                    $mealNames = $restaurantMeals->whereIn('id', $selectedMealIds)->pluck('name');
                                                @endphp
                                                @if($mealNames->isNotEmpty())
                                                    <ul class="list-unstyled mb-0 pl-0">
                                                        @foreach($mealNames as $name)
                                                            <li class="mb-1 d-flex align-items-center"><span class="list-bullet-circle mr-2"></span>{{ $name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mb-0 text-muted">—</p>
                                                @endif
                                            </div>
                                        @endif
                                        @if($pt->amenities->isNotEmpty())
                                            <div class="form-group mb-0">
                                                <label class="text-muted small font-weight-bold">Amenities</label>
                                                @php
                                                    $amenityNames = $pt->amenities->whereIn('id', $selectedAmenityIds)->pluck('name');
                                                @endphp
                                                @if($amenityNames->isNotEmpty())
                                                    <ul class="list-unstyled mb-0 pl-0">
                                                        @foreach($amenityNames as $name)
                                                            <li class="mb-1 d-flex align-items-center"><span class="list-bullet-circle mr-2"></span>{{ $name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mb-0 text-muted">—</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-muted small">Open</label>
                            <p class="mb-0">{{ (int) $organization->open === 1 ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="text-muted small">Excerpts</label>
                    <p class="mb-0">{{ $organization->excerpts ?: '—' }}</p>
                </div>

                <a href="{{ route('masterapp.organizations.index') }}" class="btn btn-secondary">Back to List</a>
                @can('edit-organization')
                <a href="{{ route('masterapp.organizations.edit', $organization->id) }}" class="btn btn-primary"><i class="fa fa-edit mr-1"></i> Edit</a>
                @endcan
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.list-bullet-circle {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    flex-shrink: 0;
}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.org-section-btn').on('click', function () {
        var target = $(this).data('target');
        $('.org-section-btn').removeClass('active');
        $(this).addClass('active');
        $('.org-section-panel').addClass('d-none');
        $('#' + target).removeClass('d-none');
    });
    $('.types-parent-btn').on('click', function () {
        var id = $(this).data('id');
        $('.types-parent-btn').removeClass('active');
        $(this).addClass('active');
        $('.types-panel').addClass('d-none');
        $('#types_panel_' + id).removeClass('d-none');
    });
    $('.types-parent-btn').first().trigger('click');
});
</script>
@endpush
