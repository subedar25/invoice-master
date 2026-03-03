@extends('masterapp.layouts.app')
@section('title', 'Create User', 'bold')
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <div class="card card-primary shadow-md">

                    {{-- CARD HEADER --}}
                    <div class="card-header">
                        <h3 class="card-title">Create User</h3>
                    </div>

                    {{-- CARD BODY --}}
                    <div class="card-body">
                            <!-- form start -->
                        <form id="userForm" action="{{ route('masterapp.users.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="col-lg-10 bordar">
                            {{-- BASIC INFO --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputFirstName">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" placeholder="Enter First name" value="{{ old('first_name') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputLastName">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Enter Last name" value="{{ old('last_name') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputEmail">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" id="email" placeholder="Enter Email address" value="{{ old('email') }}" pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$">
                                        <small id="email-error" class="text-danger" style="display: none;"></small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputPhone">Phone</label>
                                        <input type="tel" name="phone" class="form-control" placeholder="Enter Phone number" value="{{ old('phone') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- PASSWORD --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group" style="position:relative;">
                                        <label for="InputPassword">Password <span class="text-danger">*</span></label>
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
                                            <ul class="list-unstyled small">
                                                <li id="req-length"><i class="fas fa-times text-danger"></i> At least 8 characters</li>
                                                <li id="req-uppercase"><i class="fas fa-times text-danger"></i> At least one uppercase letter</li>
                                                <li id="req-lowercase"><i class="fas fa-times text-danger"></i> At least one lowercase letter</li>
                                                <li id="req-number"><i class="fas fa-times text-danger"></i> At least one number</li>
                                                <li id="req-special"><i class="fas fa-times text-danger"></i> At least one special character</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputConfirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
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

                            {{-- ROLES --}}
                        <div class="row">
                            <div class="col-sm-6">
                            <div class="form-group">
                                {{-- <label for="InputRoles">Assign Roles <span class="text-danger">*</span></label>
                                <select name="roles[]" class="form-control select2" style="width: 100%;" multiple required>
                                    @foreach($roles as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('roles', [])) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select> --}}
                                         <label for="roles">
                                            Assign Role(s) <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            id="roles"
                                            name="roles[]"
                                            {{-- class="form-control select2 select2-search select2-search--inline" --}}
                                            class="select2"
                                            multiple="multiple"
                                            {{-- data-placeholder="Select Roles" --}}
                                            style="width: 100%;"
                                            required>
                                            {{-- <option></option>  --}}
                                            @foreach($roles as $id => $name)
                                                <option value="{{ $id }}"
                                                    {{ in_array($id, old('roles', [])) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="InputPublications">Publications</label>
                                <select
                                id="publications"
                                name="publications[]"
                                {{-- class="form-control select2"  --}}
                                class="select2"
                                multiple>
                                    @foreach($publications as $publication)
                                        <option value="{{ $publication->id }}" {{ in_array($publication->id, old('publications', [])) ? 'selected' : '' }}>
                                            {{ $publication->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                            {{-- STATUS  --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputContributor">Contributor Status</label>
                                        <select name="contributor_status" class="form-control">
                                            <option value="no">No</option>
                                            <option value="current">Current</option>
                                            <option value="past">Past</option>
                                        </select>
                                    </div>
                                </div>                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- PUBLICATIONS --}}
                            {{-- <div class="row">
                            <div class="col-sm-6">
                            <div class="form-group">
                                <label for="InputPublications">Publications</label>
                                <select name="publications[]" class="form-control select2" multiple>
                                    @foreach($publications as $publication)
                                        <option value="{{ $publication->id }}" {{ in_array($publication->id, old('publications', [])) ? 'selected' : '' }}>
                                            {{ $publication->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> --}}

                            {{-- ACTIVE / DRIVER --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputDepartment">Department</label>
                                        <select name="department_id" class="form-control">
                                            <option value="">Select department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputDriver">Driver</label>
                                        <select name="driver" class="form-control">
                                            <option value="0" {{ old('driver','0') == '0' ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('driver') == '1' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- STATUS --}}
                            <div class="row">
                                 <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="InputActive">Active Status</label>
                                        <select name="active" class="form-control">
                                            <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }} selected>Active</option>
                                            <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            {{-- <div class="col-sm-6">
                            <div class="form-group">
                                <label for="InputStatus">Status</label>
                                <select name="status_id" class="form-control">
                                    <option value="">Select status</option>
                                    @foreach($statusesList as $status)
                                        <option value="{{ $status->id }}" {{ old('status_id') == $status->id ? 'selected' : '' }}>{{ $status->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                    </div>

                            {{-- STATUS NOTES --}}
                            <div class="row">
                            <div class="col-sm-6">
                            <div class="form-group">
                                <label for="InputStatusNotes" >Notes</label>
                                {{-- <input type="input" name="status_notes" id="status_notes"> --}}
                                <textarea id="status_notes" name="status_notes" class="form-control" rows="4"></textarea>
                             <span id="statusNotesCount">0</span> / 200 characters
                            </small>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                 <!-- /.card-body -->
                            {{-- card footer --}}
                            <div class="card-footer">
                                {{-- <a href="{{ route('masterapp.users.index') }}" class="btn btn-secondary"> --}}
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <span id="btn-create-text">Submit</span>
                                    <span id="btn-create-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>

                        </form>


                    {{-- </div> --}}
                </div>
            </div>
            </div><!-- /.card -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
{{-- </section> --}}
{{-- <script>
document.getElementById('added_timestamp').value = new Date().toString();
</script> --}}

@endsection


@push('scripts')





<!-- Select2 CSS -->

<!-- Select2 JS -->
<script>

// $(document).ready(function () {
//      $('.select2').select2({
//         theme: 'bootstrap4',
//          width: '100%'

//      });
//  });

$('#roles').select2({
    width: '100%',
    placeholder: 'Select roles',
    // allowClear: true,
    closeOnSelect: false
});
$('#publications').select2({
    width: '100%',
    placeholder: 'Select roles',
    // allowClear: true,
    closeOnSelect: false
});


// $(document).ready(function () {
//     $('.select2').select2({
//         theme: 'bootstrap-5',
//         width: '100%',
//         closeOnSelect: false,
//         allowClear: true
//     });
// });


// $(document).ready(function () {
//     //Initialize Select2 Elements
//     $('.select2').select2();
//     $('.select2bs4').select2({
//         theme: 'bootstrap'
//     });
//   });

  $(function () {
    $('#publicationsSelect').select2({
        placeholder: 'Select publication(s)',
        allowClear: true,
        width: '100%'
    });
});
$('#status_notes').on('input', function () {
    const len = $(this).val().length;
    $('#statusNotesCount').text(len);
});
</script>



<script>
$(function () {
//  Allow only letters + spaces (no numbers, no symbols)
$.validator.addMethod(
    'lettersOnly',
    function (value, element) {
        return this.optional(element) || /^[A-Za-z\s]+$/.test(value);
    },
    'Only letters are allowed'
);

// Allow only digits
$.validator.addMethod(
    'digitsOnly',
    function (value, element) {
        return this.optional(element) || /^[0-9]+$/.test(value);
    },
    'Only numbers are allowed'
);

    // Toast (GLOBAL)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showClass: { popup: 'animate__animated animate__fadeInUp' },
        hideClass: { popup: 'animate__animated animate__fadeOutDown' }
    });

    // Select2
    $('.select2').select2({ width: '100%' });

    // jQuery Validate + AJAX
    $('#userForm').validate({
        submitHandler: function (form) {

            // sync status notes
            // $('#status_notes_input').val(
            //     $('#status_notes_editor').html().trim()
            // );

            const $form = $(form);
            const $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true);
            $('#btn-create-text').addClass('d-none');
            $('#btn-create-spinner').removeClass('d-none');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',

                success: function (res) {
                    $('#btn-create-text').removeClass('d-none');
                    $('#btn-create-spinner').addClass('d-none');
                    window.location.href =
                        "{{ route('masterapp.users.index') }}" +
                        "?created=1&message=" + encodeURIComponent(res.message);
                },

                error: function (xhr) {
                    $btn.prop('disabled', false);
                    $('#btn-create-text').removeClass('d-none');
                    $('#btn-create-spinner').addClass('d-none');

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, (field, arr) => {
                            // Skip password confirmation mismatch
                            if (
                                field === 'password' &&
                                arr.some(e => e.toLowerCase().includes('confirmation'))
                            ) {
                                return; // continue
                            }
                            msg += arr.join('<br>') + '<br>';
                        });

                        Toast.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: msg
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Something went wrong'
                        });
                    }
                }
            });

            return false;
        },

        rules: {
            first_name: {
                required: true,
                lettersOnly: true
            },
            last_name:  {
                required: true,
                lettersOnly: true
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 8
            },
            password_confirmation: {
                required: true,
            },
            password_confirmation: {
                required: true,
            },
            roles: {
                required:true,
            },
            'roles[]': {
                required: true
            },
            phone: {
                digitsOnly: true,
                minlength: 7,
                maxlength: 10
            },
            status_notes: {
                maxlength: 200
            },
        },

        messages: {
           first_name: {
                required: "Please enter a first name",
                lettersOnly: "First name cannot contain numbers or symbols",
            },
            last_name: {
                required: "Please enter a last name",
                lettersOnly: "Last name cannot contain numbers or symbols",
            },
            email: {
                required: "Please enter an email address",
                email: "Please enter a valid email address"
            },
            password: {
                required: "Please provide a password",
                minlength: "Password must be at least 8 characters"
            },
            password_confirmation: {
                required: "Please confirm your password",
            },
            password_confirmation: {
                required: "Please confirm your password",
            },
            roles: {
                required: "Please select at least one role",
            },
             'roles[]': {
                required: "Please select at least one role"
            },
             status_notes: {
                maxlength: "Status notes cannot exceed 200 characters"
            },
        },

        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },

        highlight: function (element) {
            $(element).addClass('is-invalid');
        },

        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        }
    });



});
</script>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').on('click', function() {
        const input = $('#password');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggleConfirmPassword').on('click', function() {
        const input = $('#password_confirmation');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });


    // Password strength validation
    $('#password').on('input', function() {
        const password = $(this).val();
        if (password.length > 0) {
            $('#password-requirements').show();
        } else {
            $('#password-requirements').hide();
        }
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\{\}\[\]|\\\\:;"'<>,.?\/~`]/.test(password)
        };

        $('#req-length i').toggleClass('fa-times text-danger', !requirements.length).toggleClass('fa-check text-success', requirements.length);
        $('#req-uppercase i').toggleClass('fa-times text-danger', !requirements.uppercase).toggleClass('fa-check text-success', requirements.uppercase);
        $('#req-lowercase i').toggleClass('fa-times text-danger', !requirements.lowercase).toggleClass('fa-check text-success', requirements.lowercase);
        $('#req-number i').toggleClass('fa-times text-danger', !requirements.number).toggleClass('fa-check text-success', requirements.number);
        $('#req-special i').toggleClass('fa-times text-danger', !requirements.special).toggleClass('fa-check text-success', requirements.special);
    });

    $('#password').on('focus', function() {
        if (($(this).val() || '').length > 0) {
            $('#password-requirements').show();
        }
    });

    $('#password').on('blur', function() {
        setTimeout(function() {
            $('#password-requirements').hide();
        }, 120);
    });

    // Email uniqueness validation
    let emailCheckTimeout;
    $('#email').on('input', function() {
        const email = $(this).val();
        const errorElement = $('#email-error');

        clearTimeout(emailCheckTimeout);

        if (email && email.includes('@') && email.includes('.')) {
            emailCheckTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route("masterapp.users.check-email") }}',
                    type: 'POST',
                    data: {
                        email: email,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.exists) {
                            errorElement.text('Email already exists').show();
                        } else {
                            errorElement.hide();
                        }
                    },
                    error: function() {
                        errorElement.text('Error checking email').show();
                    }
                });
            }, 500); // 500ms delay to avoid too many requests
        } else {
            errorElement.hide();
        }
    });

    // Password match validation
    $('#password, #password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirm = $('#password_confirmation').val();
        const message = $('#passwordMatchMessage');
        if (password && confirm) {
            if (password === confirm) {
                message.text('Passwords match').removeClass('text-danger').addClass('text-success');
            } else {
                message.text('Passwords do not match').removeClass('text-success').addClass('text-danger');
            }
        } else {
            message.text('').removeClass('text-success text-danger');
        }
    });
});
</script>

@endpush
