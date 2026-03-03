@section('title', 'Location Details')

<div class="container-fluid">

    {{-- ACTION BAR --}}
    <div class="d-flex justify-content-end mb-3">
        @can('edit-location')
        <a href="{{ route('masterapp.locations.edit', $entity->id) }}" title="Edit" class="btn btn-primary" role="button">
            <i class="fa fa-edit" aria-hidden="true"></i> Edit
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-sm align-middle">
                <tbody>
                    <tr><th>Name</th><td>{{ $entity->name ?? '—' }}</td></tr>
                    <tr><th>Address</th><td>{{ $entity->address ?? '—' }}</td></tr>
                    <tr><th>Country</th><td>{{ $entity->country ?? '—' }}</td></tr>
                    <tr><th>State</th><td>{{ $entity->state ?? '—' }}</td></tr>
                    <tr><th>City</th><td>{{ $entity->city ?? '—' }}</td></tr>
                    <tr><th>Postal Code</th><td>{{ $entity->postal_code ?? '—' }}</td></tr>
                    {{-- <tr><th>Phone</th><td>{{ $entity->phone ?? '—' }}</td></tr> --}}
                    <tr><th>Latitude</th><td>{{ $entity->latitude ?? '—' }}</td></tr>
                    <tr><th>Longitude</th><td>{{ $entity->longitude ?? '—' }}</td></tr>
                    {{-- <tr><th>Show Map</th><td>{{ $entity->show_map ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Map Link</th><td>{{ $entity->show_map_link ? 'Yes' : 'No' }}</td></tr> --}}
                    {{-- <tr><th>Added Timestamp</th><td>{{ optional($entity->created_at)->format('d M Y, h:i A') ?? '—' }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ optional($entity->updated_at)->format('d M Y, h:i A') ?? '—' }}</td></tr> --}}
                </tbody>
            </table>

        </div>
    </div>

    {{-- Map Display Section --}}
    @if($entity->latitude && $entity->longitude)
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Location Map</h5>
        </div>
        <div class="card-body">
            <div id="map" style="height: 400px; width: 100%;"></div>
            <div class="mt-2">
                <a href="https://www.google.com/maps?q={{ $entity->latitude }},{{ $entity->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-external-link"></i> View on Google Maps
                </a>
            </div>
        </div>
    </div>
    @endif

</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
    </div>
@endif

@push('scripts')
<script>
    // Toast notifications
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showClass: { popup: 'animate__animated animate__fadeInUp' },
        hideClass: { popup: 'animate__animated animate__fadeOutDown' }
    });

    // Initialize map if coordinates are available
    @if($entity->latitude && $entity->longitude)
    function initMap() {
        const location = { lat: {{ $entity->latitude }}, lng: {{ $entity->longitude }} };
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: location,
        });
        const marker = new google.maps.Marker({
            position: location,
            map: map,
            title: '{{ $entity->name }}'
        });
    }

    // Load Google Maps API
    $(document).ready(function() {
        if (typeof google !== 'undefined' && google.maps) {
            initMap();
        } else {
            // Load Google Maps API if not already loaded
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBhoZ9rUnu4_Zrp8OTOAllcD1p7sfaRIsc&callback=initMap';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }
    });
    @endif
</script>
@endpush
