@extends('masterapp.layouts.app')

@section('title', 'Create User')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-primary shadow-md">
                <div class="card-header">
                    <h3 class="card-title">Create User</h3>
                </div>

                <form id="userForm" action="{{ route('masterapp.users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
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
                                    <div class="form-group" style="position: relative;">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" id="password" autocomplete="new-password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="password-requirements" class="mt-2" style="display:none; position:absolute; left:15px; right:15px; top:100%; z-index:1050; background:#fff; border:1px solid #ced4da; border-radius:.375rem; padding:8px 10px; box-shadow:0 6px 18px rgba(0,0,0,.12);">
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
                                                <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <span id="passwordMatchMessage" class="small"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
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

                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Enter address">
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="Enter city">
                                    </div>
                                </div>
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
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Photo</label>
                                        <input type="file" name="photo" class="form-control-file" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Other Documents</label>
                                        <input type="file" name="other_documents[]" class="form-control-file" multiple>
                                    </div>
                                </div>
                            </div>

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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Active Status</label>
                                        <select name="active" class="form-control">
                                            <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

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

</script>
@endpush
