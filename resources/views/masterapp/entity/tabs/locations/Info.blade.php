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
                    {{-- <tr><th>Show Map</th><td>{{ $entity->show_map ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Map Link</th><td>{{ $entity->show_map_link ? 'Yes' : 'No' }}</td></tr> --}}
                    {{-- <tr><th>Added Timestamp</th><td>{{ optional($entity->created_at)->format('d M Y, h:i A') ?? '—' }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ optional($entity->updated_at)->format('d M Y, h:i A') ?? '—' }}</td></tr> --}}
                </tbody>
            </table>

        </div>
    </div>

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

</script>
@endpush
