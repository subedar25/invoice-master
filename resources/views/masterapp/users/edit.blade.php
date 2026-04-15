@extends('masterapp.layouts.app')

@section('title', 'User Edit')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary shadow-md">
                <div class="card-header">
                    <h3 class="card-title">User Edit</h3>
                </div>

                <form id="userEditForm" action="{{ route('masterapp.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="col-lg-10 bordar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" placeholder="Enter first name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" placeholder="Enter last name">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @can('edit-email')
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" id="InputEmail" value="{{ old('email', $user->email) }}" placeholder="Enter email">
                                            <small id="edit-email-error" class="text-danger" style="display: none;"></small>
                                        @else
                                            <label>Email</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text text-muted"><i class="fas fa-lock"></i></span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="email" value="{{ $user->email }}">
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone number</label>
                                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Enter phone number">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" id="InputPassword" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="toggleEditPassword" style="cursor: pointer;">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" class="form-control" id="InputConfirmPassword" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="toggleEditConfirmPassword" style="cursor: pointer;">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <span id="editPasswordMatchMessage" class="small"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Assign Role(s) <span class="text-danger">*</span></label>
                                        <select name="roles[]" multiple required class="form-control select2" id="InputRoles">
                                            @foreach($roles as $id => $name)
                                                <option value="{{ $id }}" {{ in_array($id, old('roles', $user->roles->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Organizations</label>
                                        @php $selectedOrganizations = old('organization_ids', $user->organizations->pluck('id')->toArray()); @endphp
                                        <select id="organization_ids" name="organization_ids[]" multiple class="form-control select2" style="width: 100%;">
                                            @foreach($organizations as $organization)
                                                <option value="{{ $organization->id }}" {{ in_array($organization->id, $selectedOrganizations) ? 'selected' : '' }}>
                                                    {{ $organization->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department_id" class="form-control">
                                            <option value="">Select department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reporting Manager</label>
                                        <select name="reporting_manager_id" class="form-control select2" style="width: 100%;">
                                            <option value="">Select reporting manager</option>
                                            @foreach($reportingManagers as $manager)
                                                <option value="{{ $manager->id }}" {{ old('reporting_manager_id', $user->reporting_manager_id) == $manager->id ? 'selected' : '' }}>
                                                    {{ trim($manager->first_name . ' ' . $manager->last_name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}" placeholder="Enter address">
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}" placeholder="Enter city">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" name="state" class="form-control" value="{{ old('state', $user->state) }}" placeholder="Enter state">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Pincode</label>
                                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $user->pincode) }}" placeholder="Enter pincode">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Photo</label>
                                        <input type="file" name="photo" class="form-control-file" accept="image/*">
                                        @if($user->photo)
                                            <div class="mt-2">
                                                <img src="{{ asset($user->photo) }}" alt="User Photo" style="max-height: 90px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Other Documents</label>
                                        <input type="file" name="other_documents[]" class="form-control-file" multiple>
                                        @if($user->userDocuments->isNotEmpty())
                                            <div class="mt-2">
                                                @foreach($user->userDocuments as $document)
                                                    <div><a href="{{ asset($document->file_path) }}" target="_blank">{{ $document->file_name }}</a></div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Active Status</label>
                                        <select name="active" class="form-control">
                                            <option value="1" {{ old('active', $user->active) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('active', $user->active) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="status_notes" id="status_notes" class="form-control" rows="4" maxlength="200">{{ old('status_notes', $user->status_notes) }}</textarea>
                                        <small class="text-muted">
                                            <span id="statusNotesCount">{{ strlen(old('status_notes', $user->status_notes ?? '')) }}</span> / 200 characters
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="change_password" value="1">
                    </div>

                    <div class="card-footer">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span id="btn-edit-text">Submit</span>
                            <span id="btn-edit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});

$('#status_notes').on('input', function () {
    $('#statusNotesCount').text($(this).val().length);
});
</script>
@endpush
