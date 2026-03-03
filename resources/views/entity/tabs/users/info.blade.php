@section('title', 'User Details')

<div class="container-fluid">

    {{-- ACTION BAR --}}
    <div class="d-flex justify-content-end mb-3">
    <button type="button"
        class="btn btn-primary"
        data-toggle="modal"
        data-target="#passwordModal">
    <i class="fa fa-key"></i> Change Password
    </button>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-sm align-middle">
                <tbody>
                    <tr><th>Name</th><td>{{ $entity->first_name ?? '—' }} {{ $entity->last_name }}</td></tr>
                    <tr><th>Email</th><td>{{ $entity->email ?? '—' }}</td></tr>
                    <tr><th>Phone</th><td>{{ $entity->phone ?? '—' }}</td></tr>
                    <tr><th>Change Password</th><td>{{ $entity->change_password ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Active</th><td>{{ $entity->active ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Role</th><td>{{ $entity->roles->pluck('name')->implode(', ') ?: '—' }}</td></tr>
                    <tr><th>Permissions</th><td>{{ $entity->permissions->pluck('name')->implode(', ') ?: '—' }}</td></tr>
                    <tr><th>Added Timestamp</th><td>{{ optional($entity->created_at)->format('d M Y, h:i A') ?? '—' }}</td></tr>
                    <tr><th>Driver</th><td>{{ $entity->driver ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Status</th><td>{{ optional($entity->status)->label ?? '—' }}</td></tr>
                    <tr><th>Status Notes</th><td>{!! $entity->status_notes ?: '—' !!}</td></tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form method="POST"
                  action="{{ route('users.password.update', $entity->id) }}">
                @csrf


                <input type="hidden"
                       name="username"
                       autocomplete="username"
                       value="{{ $entity->email ?? $entity->first_name }}">

                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               autocomplete="new-password"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control"
                               autocomplete="new-password"
                               required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary btn-sm"
                            data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-sm">
                        Change Password
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
    </div>
@endif
