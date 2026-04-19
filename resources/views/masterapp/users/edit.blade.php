@extends('masterapp.layouts.app')

@section('title', 'User Edit')

@push('styles')
<style>
    .user-edit-form-card .card-body {
        overflow: visible;
    }
    .user-create-attachments {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        background: #f8f9fa;
        padding: 1.25rem;
    }
    .user-create-attachments h5 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #343a40;
    }
    .user-create-photo-preview {
        width: 120px;
        height: 120px;
        border-radius: 0.375rem;
        border: 1px dashed #ced4da;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .user-create-photo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }
    .user-create-photo-preview .user-create-photo-placeholder {
        color: #adb5bd;
        font-size: 2rem;
    }
    .user-create-doc-list {
        font-size: 0.875rem;
        min-height: 2rem;
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
    }
    .user-create-doc-list:empty {
        display: none;
    }
    .user-create-doc-list li {
        padding: 0.15rem 0;
        word-break: break-all;
    }
    .user-edit-doc-existing .doc-container-row {
        border: 1px solid #e9ecef !important;
        background: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary shadow-md user-edit-form-card">
                <div class="card-header">
                    <h3 class="card-title">User Edit</h3>
                </div>

                <form id="userEditForm" action="{{ route('masterapp.users.update', $user->id) }}" method="POST" enctype="multipart/form-data" data-exclude-user-id="{{ $user->id }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong>There were problems with your submission:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
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
                                <div class="{{ auth()->user()->isSystemUser() ? 'col-md-6' : 'col-md-12' }}">
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
                                @if(auth()->user()->isSystemUser())
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
                                @endif
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

                            <div class="row align-items-start">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control" rows="2" placeholder="Enter address">{{ old('address', $user->address) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}" placeholder="Enter city">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Active Status</label>
                                        <select name="active" class="form-control">
                                            <option value="1" {{ old('active', $user->active) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('active', $user->active) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <div class="user-create-attachments">
                                    <h5 class="mb-3"><i class="fas fa-paperclip mr-1 text-muted"></i> Photo &amp; documents</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="d-block font-weight-bold">Profile photo <span class="text-muted font-weight-normal">(optional)</span></label>
                                            <p class="small text-muted mb-2">JPG, PNG or GIF. Max 2&nbsp;MB. Choose a new file to replace the current photo.</p>
                                            @if($user->photo)
                                                <input type="hidden" name="remove_photo" id="remove_photo_input" value="0">
                                            @endif
                                            <div class="custom-file">
                                                <input type="file" name="photo" id="user_edit_photo" class="custom-file-input" accept="image/*">
                                                <label class="custom-file-label" for="user_edit_photo" data-browse="Browse">Choose new image…</label>
                                            </div>
                                            <div id="user_edit_photo_preview" class="user-create-photo-preview">
                                                @if($user->photo)
                                                    <div id="photo-preview-container" class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                                                        <img src="{{ asset($user->photo) }}" alt="Current photo" class="w-100 h-100" style="object-fit: cover;">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger position-absolute btn-remove-photo"
                                                            data-delete-url="{{ route('masterapp.users.photo.destroy', $user) }}"
                                                            style="top: 4px; right: 4px; border-radius: 50%; width: 28px; height: 28px; padding: 0; line-height: 1;"
                                                            title="Remove photo"
                                                        >
                                                            <i class="fas fa-trash" style="font-size: 11px;"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="user-create-photo-placeholder"><i class="fas fa-user"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3 mt-md-0">
                                            <label class="d-block font-weight-bold">Other documents <span class="text-muted font-weight-normal">(optional)</span></label>
                                            <p class="small text-muted mb-2">PDF, images, or Office files. Up to 10&nbsp;MB each; you can add multiple new files.</p>
                                            <div class="custom-file">
                                                <input type="file" name="other_documents[]" id="user_edit_other_documents" class="custom-file-input" multiple>
                                                <label class="custom-file-label" for="user_edit_other_documents" data-browse="Browse">Add more files…</label>
                                            </div>
                                            <ul id="user_edit_docs_new_list" class="user-create-doc-list list-unstyled mb-0"></ul>
                                            @if($user->userDocuments->isNotEmpty())
                                                <p class="small font-weight-bold text-secondary mt-3 mb-2">Uploaded files</p>
                                                <div class="user-edit-doc-existing" id="documents-container">
                                                    @foreach($user->userDocuments as $document)
                                                        <div class="mb-2 d-flex align-items-center p-2 rounded doc-container-row" id="doc-container-{{ $document->id }}">
                                                            <i class="fas fa-file-alt text-secondary mr-2"></i>
                                                            <a href="{{ asset($document->file_path) }}" target="_blank" class="mr-auto text-truncate" style="max-width: 75%;">{{ $document->file_name }}</a>
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
                                            @else
                                                <div id="remove-documents-inputs"></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->isSystemUser())
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
                            </div>
                            @endif

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

    var rmOptionsUrl = @json(route('masterapp.users.reporting-managers-options'));
    var rolesOptionsUrl = @json(route('masterapp.users.roles-options'));
    var $orgSelectEdit = $('#organization_ids');
    function syncReportingManagersFromOrgsEdit() {
        if (!$orgSelectEdit.length) return;
        var orgIds = $orgSelectEdit.val() || [];
        if (!$.isArray(orgIds)) orgIds = orgIds ? [orgIds] : [];
        var params = { organization_ids: orgIds };
        var ex = $('#userEditForm').data('excludeUserId');
        if (ex) params.exclude_user_id = ex;
        $.get(rmOptionsUrl, params).done(function (res) {
            var $rm = $('select[name="reporting_manager_id"]');
            var cur = $rm.val();
            $rm.empty().append($('<option></option>').val('').text('Select reporting manager'));
            (res.managers || []).forEach(function (m) {
                var label = $.trim((m.first_name || '') + ' ' + (m.last_name || ''));
                $rm.append($('<option></option>').attr('value', m.id).text(label));
            });
            if (cur && $rm.find('option[value="' + cur + '"]').length) {
                $rm.val(cur).trigger('change');
            } else {
                $rm.val('').trigger('change');
            }
        });
    }
    function syncRolesFromOrgsEdit() {
        if (!$orgSelectEdit.length) return;
        var orgIds = $orgSelectEdit.val() || [];
        if (!$.isArray(orgIds)) orgIds = orgIds ? [orgIds] : [];
        $.get(rolesOptionsUrl, { organization_ids: orgIds }).done(function (res) {
            var $rs = $('#InputRoles');
            var cur = $rs.val() || [];
            if (!$.isArray(cur)) cur = cur ? [cur] : [];
            $rs.empty();
            (res.roles || []).forEach(function (r) {
                $rs.append($('<option></option>').attr('value', r.id).text(r.name));
            });
            var kept = cur.filter(function (id) {
                return $rs.find('option[value="' + id + '"]').length;
            });
            $rs.val(kept).trigger('change');
        });
    }
    if ($orgSelectEdit.length) {
        $orgSelectEdit.on('change', function () {
            syncReportingManagersFromOrgsEdit();
            syncRolesFromOrgsEdit();
        });
    }

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
            if ($('#remove_photo_input').length) {
                $('#remove_photo_input').val('1');
            } else {
                $('<input>', { type: 'hidden', name: 'remove_photo', id: 'remove_photo_input', value: '1' }).insertBefore('#user_edit_photo_preview');
            }
            $('#user_edit_photo_preview').html(
                '<span class="user-create-photo-placeholder"><i class="fas fa-user"></i></span>'
            );
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
                if ($('#remove_photo_input').length) {
                    $('#remove_photo_input').val('1');
                } else {
                    $('<input>', { type: 'hidden', name: 'remove_photo', id: 'remove_photo_input', value: '1' }).insertBefore('#user_edit_photo_preview');
                }
                $('#user_edit_photo_preview').html(
                    '<span class="user-create-photo-placeholder"><i class="fas fa-user"></i></span>'
                );
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

    var $editPhotoInput = $('#user_edit_photo');
    var $editPhotoPreview = $('#user_edit_photo_preview');
    $editPhotoInput.on('change', function () {
        var file = this.files && this.files[0];
        var $label = $(this).next('.custom-file-label');
        if (!file) {
            $label.text('Choose new image…');
            return;
        }
        $label.text(file.name);
        if (file.type.indexOf('image/') === 0) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $editPhotoPreview.empty().append(
                    $('<div>', { class: 'position-relative w-100 h-100 d-flex align-items-center justify-content-center' }).append(
                        $('<img>', {
                            src: e.target.result,
                            alt: 'Preview',
                            class: 'w-100 h-100',
                            css: { objectFit: 'cover' },
                        }),
                        $('<span>', {
                            class: 'badge badge-info position-absolute',
                            css: { bottom: '6px', left: '50%', transform: 'translateX(-50%)', fontSize: '10px', whiteSpace: 'nowrap' },
                            text: 'New — save to apply',
                        })
                    )
                );
            };
            reader.readAsDataURL(file);
        } else {
            $editPhotoPreview.empty().append(
                '<span class="user-create-photo-placeholder"><i class="fas fa-image"></i></span>'
            );
        }
    });

    var $editDocsInput = $('#user_edit_other_documents');
    var $editDocsNewList = $('#user_edit_docs_new_list');
    $editDocsInput.on('change', function () {
        var files = this.files;
        var $label = $(this).next('.custom-file-label');
        if (!files || !files.length) {
            $label.text('Add more files…');
            $editDocsNewList.empty();
            return;
        }
        $label.text(files.length + ' new file(s) selected');
        $editDocsNewList.empty();
        for (var i = 0; i < files.length; i++) {
            $editDocsNewList.append(
                $('<li>').append(
                    $('<i class="far fa-file mr-1 text-muted"></i>'),
                    document.createTextNode(files[i].name + ' (pending upload)')
                )
            );
        }
    });
});
</script>
@endpush
