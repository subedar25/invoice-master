@extends('masterapp.layouts.app')

@section('title', 'Create User')

@push('styles')
<style>
    /* Allow password hint popover to extend past card edges */
    .user-create-form-card .card-body {
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-primary shadow-md user-create-form-card">
                <div class="card-header">
                    <h3 class="card-title">Create User</h3>
                </div>

                <form id="userForm" action="{{ route('masterapp.users.store') }}" method="POST" enctype="multipart/form-data" data-redirect-after-create="{{ route('masterapp.users.index') }}">
                    @csrf
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
                                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" placeholder="Enter first name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" placeholder="Enter last name">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="Enter email address">
                                        <small id="email-error" class="text-danger" style="display: none;"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Enter phone number">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group js-create-password-field" style="position: relative; overflow: visible; z-index: 2;">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" id="password" autocomplete="new-password" aria-describedby="password-requirements">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="togglePassword" role="button" tabindex="0" style="cursor: pointer;" title="Show password" aria-label="Show password">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="password-requirements" class="mt-2" style="display:none; position:absolute; left:0; right:0; top:100%; z-index: 1060; background:#fff; border:1px solid #ced4da; border-radius:.375rem; padding:8px 10px; box-shadow:0 6px 18px rgba(0,0,0,.12); max-height: 220px; overflow-y: auto;">
                                            <small>Password must contain:</small>
                                            <ul class="list-unstyled small mb-0">
                                                <li id="req-length"><i class="fas fa-times text-danger"></i> At least 8 characters</li>
                                                <li id="req-uppercase"><i class="fas fa-times text-danger"></i> At least one uppercase letter</li>
                                                <li id="req-lowercase"><i class="fas fa-times text-danger"></i> At least one lowercase letter</li>
                                                <li id="req-number"><i class="fas fa-times text-danger"></i> At least one number</li>
                                                <li id="req-special"><i class="fas fa-times text-danger"></i> At least one special character</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="toggleConfirmPassword" role="button" tabindex="0" style="cursor: pointer;" title="Show password" aria-label="Show password">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <span id="passwordMatchMessage" class="small"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="{{ auth()->user()->isSystemUser() ? 'col-md-6' : 'col-md-12' }}">
                                    <div class="form-group">
                                        <label>Assign Role(s) <span class="text-danger">*</span></label>
                                        <select id="roles" name="roles[]" class="select2" multiple="multiple" style="width: 100%;" required>
                                            @foreach($roles as $id => $name)
                                                <option value="{{ $id }}" {{ in_array($id, old('roles', [])) ? 'selected' : '' }}>
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
                                        <select id="organization_ids" name="organization_ids[]" class="select2" multiple="multiple" style="width: 100%;">
                                            @foreach($organizations as $organization)
                                                <option value="{{ $organization->id }}" {{ in_array($organization->id, old('organization_ids', [])) ? 'selected' : '' }}>
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
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
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
                                            @foreach($designations as $designation)
                                                <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
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
                                                <option value="{{ $manager->id }}" {{ old('reporting_manager_id') == $manager->id ? 'selected' : '' }}>
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
                                        <textarea name="address" class="form-control" rows="2" placeholder="Enter address">{{ old('address') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="Enter city">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" name="state" class="form-control" value="{{ old('state') }}" placeholder="Enter state">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Pincode</label>
                                        <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" placeholder="Enter pincode">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Active Status</label>
                                        <select name="active" class="form-control">
                                            <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inactive</option>
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
                                            <p class="small text-muted mb-2">JPG, PNG or GIF. Max 2&nbsp;MB.</p>
                                            <div class="custom-file">
                                                <input type="file" name="photo" id="user_create_photo" class="custom-file-input" accept="image/*">
                                                <label class="custom-file-label" for="user_create_photo" data-browse="Browse">Choose image…</label>
                                            </div>
                                            <div id="user_create_photo_preview" class="user-create-photo-preview" aria-hidden="true">
                                                <span class="user-create-photo-placeholder"><i class="fas fa-user"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3 mt-md-0">
                                            <label class="d-block font-weight-bold">Other documents <span class="text-muted font-weight-normal">(optional)</span></label>
                                            <p class="small text-muted mb-2">PDF, images, or Office files. Up to 10&nbsp;MB each; you can select multiple files.</p>
                                            <div class="custom-file">
                                                <input type="file" name="other_documents[]" id="user_create_other_documents" class="custom-file-input" multiple>
                                                <label class="custom-file-label" for="user_create_other_documents" data-browse="Browse">Choose files…</label>
                                            </div>
                                            <ul id="user_create_docs_list" class="user-create-doc-list list-unstyled mb-0"></ul>
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
                                            @php $userType = old('user_type', 'user'); @endphp
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

                    <div class="card-footer">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span id="btn-create-text">Submit</span>
                            <span id="btn-create-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
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
$(function () {
    $('#roles, #organization_ids').select2({
        width: '100%',
        placeholder: 'Select options',
        closeOnSelect: false
    });

    $('select[name="reporting_manager_id"]').select2({
        width: '100%',
        placeholder: 'Select reporting manager',
        allowClear: true
    });

    var rmOptionsUrl = @json(route('masterapp.users.reporting-managers-options'));
    var rolesOptionsUrl = @json(route('masterapp.users.roles-options'));
    var $orgSelectCreate = $('#organization_ids');
    function syncReportingManagersFromOrgsCreate() {
        if (!$orgSelectCreate.length) return;
        var orgIds = $orgSelectCreate.val() || [];
        if (!$.isArray(orgIds)) orgIds = orgIds ? [orgIds] : [];
        $.get(rmOptionsUrl, { organization_ids: orgIds }).done(function (res) {
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
    function syncRolesFromOrgsCreate() {
        if (!$orgSelectCreate.length) return;
        var orgIds = $orgSelectCreate.val() || [];
        if (!$.isArray(orgIds)) orgIds = orgIds ? [orgIds] : [];
        $.get(rolesOptionsUrl, { organization_ids: orgIds }).done(function (res) {
            var $rs = $('#roles');
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
    $orgSelectCreate.on('change', function () {
        syncReportingManagersFromOrgsCreate();
        syncRolesFromOrgsCreate();
    });

    var $pwd = $('#password');
    var $confirm = $('#password_confirmation');
    var $req = $('#password-requirements');
    var passwordFocused = false;

    function bindPasswordToggle(toggleSelector, inputSelector) {
        $(document).on('click', toggleSelector, function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $input = $(inputSelector);
            var $icon = $(this).find('i');
            if (!$input.length) return;
            var show = $input.attr('type') === 'password';
            $input.attr('type', show ? 'text' : 'password');
            $icon.toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
            $(this).attr('title', show ? 'Hide password' : 'Show password');
        });
    }

    bindPasswordToggle('#togglePassword', '#password');
    bindPasswordToggle('#toggleConfirmPassword', '#password_confirmation');

    function setRequirementState(selector, isValid) {
        var $item = $(selector);
        var $icon = $item.find('i');
        $icon
            .toggleClass('fa-check text-success', isValid)
            .toggleClass('fa-times text-danger', !isValid);
    }

    function updatePasswordRequirements() {
        var password = String($pwd.val() || '');
        var hasValue = password.length > 0;

        var hasMinLength = password.length >= 8;
        var hasUppercase = /[A-Z]/.test(password);
        var hasLowercase = /[a-z]/.test(password);
        var hasNumber = /[0-9]/.test(password);
        var hasSpecial = /[^A-Za-z0-9]/.test(password);
        var isAllValid = hasMinLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;

        var showPanel = (passwordFocused || hasValue) && !isAllValid;
        $req.toggle(showPanel);

        if (!hasValue && !passwordFocused) {
            setRequirementState('#req-length', false);
            setRequirementState('#req-uppercase', false);
            setRequirementState('#req-lowercase', false);
            setRequirementState('#req-number', false);
            setRequirementState('#req-special', false);
            return;
        }

        setRequirementState('#req-length', hasMinLength);
        setRequirementState('#req-uppercase', hasUppercase);
        setRequirementState('#req-lowercase', hasLowercase);
        setRequirementState('#req-number', hasNumber);
        setRequirementState('#req-special', hasSpecial);
    }

    function updatePasswordMatchMessage() {
        var password = $pwd.val() || '';
        var confirmVal = $confirm.val() || '';
        var $msg = $('#passwordMatchMessage');
        if (password && confirmVal) {
            if (password === confirmVal) {
                $msg.removeClass('text-danger').addClass('text-success').text('Passwords match.');
            } else {
                $msg.removeClass('text-success').addClass('text-danger').text('Passwords do not match.');
            }
        } else {
            $msg.removeClass('text-success text-danger').text('');
        }
    }

    $pwd.on('focus', function () {
        passwordFocused = true;
        updatePasswordRequirements();
    });

    $pwd.on('input', function () {
        updatePasswordRequirements();
        updatePasswordMatchMessage();
    });

    $pwd.on('blur', function () {
        passwordFocused = false;
        updatePasswordRequirements();
    });

    $confirm.on('input', updatePasswordMatchMessage);

    var $photoInput = $('#user_create_photo');
    var $photoPreview = $('#user_create_photo_preview');
    $photoInput.on('change', function () {
        var file = this.files && this.files[0];
        var $label = $(this).next('.custom-file-label');
        if (file) {
            $label.text(file.name);
            if (file.type.indexOf('image/') === 0) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $photoPreview.empty().append(
                        $('<img>', { src: e.target.result, alt: 'Preview' })
                    );
                };
                reader.readAsDataURL(file);
            } else {
                $photoPreview.empty().append(
                    '<span class="user-create-photo-placeholder"><i class="fas fa-image"></i></span>'
                );
            }
        } else {
            $label.text('Choose image…');
            $photoPreview.empty().append(
                '<span class="user-create-photo-placeholder"><i class="fas fa-user"></i></span>'
            );
        }
    });

    var $docsInput = $('#user_create_other_documents');
    var $docsList = $('#user_create_docs_list');
    $docsInput.on('change', function () {
        var files = this.files;
        var $label = $(this).next('.custom-file-label');
        if (!files || !files.length) {
            $label.text('Choose files…');
            $docsList.empty();
            return;
        }
        $label.text(files.length + ' file(s) selected');
        $docsList.empty();
        for (var i = 0; i < files.length; i++) {
            $docsList.append(
                $('<li>').append(
                    $('<i class="far fa-file mr-1 text-muted"></i>'),
                    document.createTextNode(files[i].name)
                )
            );
        }
    });
});
</script>
@endpush
