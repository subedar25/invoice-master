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
                                    <div class="form-group" style="position: relative;">
                                        <label>Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="InputPassword" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="toggleEditPassword" style="cursor: pointer;">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="edit-password-requirements" class="mt-2" style="display:none; position:absolute; left:15px; right:15px; top:100%; z-index:1050; background:#fff; border:1px solid #ced4da; border-radius:.375rem; padding:8px 10px; box-shadow:0 6px 18px rgba(0,0,0,.12);">
                                            <small>Password must contain:</small>
                                            <ul class="list-unstyled small mb-0">
                                                <li id="edit-req-length"><i class="fas fa-times text-danger"></i> At least 8 characters</li>
                                                <li id="edit-req-uppercase"><i class="fas fa-times text-danger"></i> At least one uppercase letter</li>
                                                <li id="edit-req-lowercase"><i class="fas fa-times text-danger"></i> At least one lowercase letter</li>
                                                <li id="edit-req-number"><i class="fas fa-times text-danger"></i> At least one number</li>
                                                <li id="edit-req-special"><i class="fas fa-times text-danger"></i> At least one special character</li>
                                            </ul>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="InputConfirmPassword" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="toggleEditConfirmPassword" style="cursor: pointer;">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        @error('password_confirmation')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
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
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Designation</label>
                                        <select name="designation_id" class="form-control">
                                            <option value="">Select designation</option>
                                            @foreach ($designations as $designation)
                                                <option value="{{ $designation->id }}" {{ old('designation_id', $user->designation_id) == $designation->id ? 'selected' : '' }}>
                                                    {{ $designation->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                            <div class="mt-2 position-relative d-inline-block" id="photo-preview-container">
                                                <img src="{{ asset($user->photo) }}" alt="User Photo" style="max-height: 90px; border: 1px solid #dee2e6; border-radius: 4px; padding: 2px;">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger position-absolute btn-remove-photo"
                                                    data-delete-url="{{ route('masterapp.users.photo.destroy', $user) }}"
                                                    style="top: -8px; right: -8px; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 1;"
                                                    title="Remove photo"
                                                >
                                                    <i class="fas fa-trash" style="font-size: 10px;"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="remove_photo" id="remove_photo_input" value="0">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Other Documents</label>
                                        <input type="file" name="other_documents[]" class="form-control-file" multiple>
                                        @if($user->userDocuments->isNotEmpty())
                                            <div class="mt-2" id="documents-container">
                                                @foreach($user->userDocuments as $document)
                                                    <div class="mb-2 d-flex align-items-center bg-light p-2 rounded doc-container-row" id="doc-container-{{ $document->id }}" style="border: 1px solid #e9ecef;">
                                                        <i class="fas fa-file-alt text-secondary mr-2"></i>
                                                        <a href="{{ asset($document->file_path) }}" target="_blank" class="mr-auto text-truncate" style="max-width: 80%;">{{ $document->file_name }}</a>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger ml-2 btn-remove-document"
                                                            data-document-id="{{ $document->id }}"
                                                            data-delete-url="{{ route('masterapp.users.documents.destroy', ['user' => $user->id, 'document' => $document->id]) }}"
                                                            style="padding: 2px 6px;"
                                                            title="Remove document"
                                                        >
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div id="remove-documents-inputs"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>User Type <span class="text-danger">*</span></label>
                                        <select name="user_type" class="form-control">
                                            @php $userType = old('user_type', $user->user_type ?? 'user'); @endphp
                                            <option value="systemuser" {{ $userType === 'systemuser' ? 'selected' : '' }}>systemuser</option>
                                            <option value="superadmin" {{ $userType === 'superadmin' ? 'selected' : '' }}>superadmin</option>
                                            <option value="admin" {{ $userType === 'admin' ? 'selected' : '' }}>admin</option>
                                            <option value="user" {{ $userType === 'user' ? 'selected' : '' }}>user</option>
                                        </select>
                                    </div>
                                </div>
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

    function bindPasswordToggle(toggleSelector, inputSelector) {
        $(document).on('click', toggleSelector, function () {
            var $toggle = $(this);
            var $input = $(inputSelector);
            var $icon = $toggle.find('i');

            if (!$input.length) return;

            var show = $input.attr('type') === 'password';
            $input.attr('type', show ? 'text' : 'password');
            $icon.toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
        });
    }

    bindPasswordToggle('#toggleEditPassword', '#InputPassword');
    bindPasswordToggle('#toggleEditConfirmPassword', '#InputConfirmPassword');

    function setRequirementState(selector, isValid) {
        var $item = $(selector);
        var $icon = $item.find('i');
        $icon
            .toggleClass('fa-check text-success', isValid)
            .toggleClass('fa-times text-danger', !isValid);
    }

    function updatePasswordRequirements(value) {
        var password = String(value || '');
        var hasValue = password.length > 0;

        var hasMinLength = password.length >= 8;
        var hasUppercase = /[A-Z]/.test(password);
        var hasLowercase = /[a-z]/.test(password);
        var hasNumber = /[0-9]/.test(password);
        var hasSpecial = /[^A-Za-z0-9]/.test(password);
        var isAllValid = hasMinLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;

        $('#edit-password-requirements').toggle(hasValue && !isAllValid);
        if (!hasValue) {
            setRequirementState('#edit-req-length', false);
            setRequirementState('#edit-req-uppercase', false);
            setRequirementState('#edit-req-lowercase', false);
            setRequirementState('#edit-req-number', false);
            setRequirementState('#edit-req-special', false);
            return;
        }

        setRequirementState('#edit-req-length', hasMinLength);
        setRequirementState('#edit-req-uppercase', hasUppercase);
        setRequirementState('#edit-req-lowercase', hasLowercase);
        setRequirementState('#edit-req-number', hasNumber);
        setRequirementState('#edit-req-special', hasSpecial);
    }

    $('#InputPassword').on('input', function () {
        updatePasswordRequirements($(this).val());
    });

    $(document).on('click', '.btn-remove-photo', function() {
        var $btn = $(this);
        var deleteUrl = $btn.data('delete-url');

        var doFallback = function () {
            $('#photo-preview-container').remove();
            $('#remove_photo_input').val('1');
        };

        var confirmAndDelete = function () {
            if (!deleteUrl) return doFallback();

            $btn.prop('disabled', true);
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            }).done(function () {
                $('#photo-preview-container').remove();
                $('#remove_photo_input').val('0');
            }).fail(function () {
                doFallback();
            }).always(function () {
                $btn.prop('disabled', false);
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Remove photo?',
                text: 'This will permanently delete the current photo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
            }).then(function (result) {
                if (result.isConfirmed) confirmAndDelete();
            });
        } else if (window.confirm('Remove photo? This will permanently delete the current photo.')) {
            confirmAndDelete();
        }
    });

    $(document).on('click', '.btn-remove-document', function() {
        var $btn = $(this);
        var id = $btn.data('document-id');
        var deleteUrl = $btn.data('delete-url');

        var doFallback = function () {
            $('#doc-container-' + id).remove();
            $('#remove-documents-inputs').append('<input type="hidden" name="remove_documents[]" value="' + id + '">');
        };

        var confirmAndDelete = function () {
            if (!deleteUrl) return doFallback();

            $btn.prop('disabled', true);
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            }).done(function () {
                $('#doc-container-' + id).remove();
                // No need to append hidden input since server already deleted it.
            }).fail(function () {
                doFallback();
            }).always(function () {
                $btn.prop('disabled', false);
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Remove document?',
                text: 'This will permanently delete the document.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
            }).then(function (result) {
                if (result.isConfirmed) confirmAndDelete();
            });
        } else if (window.confirm('Remove document? This will permanently delete the document.')) {
            confirmAndDelete();
        }
    });
});
</script>
@endpush
