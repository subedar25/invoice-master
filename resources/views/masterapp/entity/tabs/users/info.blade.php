@section('title', 'User Details')

<div class="container-fluid">

    {{-- ACTION BAR --}}
    <div class="d-flex justify-content-end mb-3">
        {{-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#passwordModal">
            <i class="fa fa-edit"></i> Edit
        </button>  --}}
        @can('edit-user')
        <a href="{{ route('masterapp.users.edit', $entity->id) }}" title="Edit" class="btn btn-primary" type="button" style="margin-right: 10px;">
            <i class="fa fa-edit" aria-hidden="true"> Edit</i>
            </a>
        @endcan
        {{-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#passwordModal">
            <i class="fa fa-key"></i> Change Password
        </button> --}}

    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-sm align-middle">
                <tbody>
                    <tr><th>Name</th><td>{{ $entity->first_name ?? '—' }} {{ $entity->last_name }}</td></tr>
                    <tr><th>Email</th><td>{{ $entity->email ?? '—' }}</td></tr>
                    <tr><th>Phone</th><td>{{ $entity->phone ?? '—' }}</td></tr>
                    {{-- <tr><th>Change Password</th><td>{{ $entity->change_password ? 'Yes' : 'No' }}</td></tr> --}}
                    <tr><th>Active</th><td>{{ $entity->active ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Role</th><td>{{ $entity->roles->pluck('name')->implode(', ') ?: '—' }}</td></tr>
                    {{-- <tr><th>Permissions</th><td>{{ $entity->permissions->pluck('name')->implode(', ') ?: '—' }}</td></tr> --}}
                    <tr><th>Added Timestamp</th><td>{{ optional($entity->created_at)->format('d M Y, h:i A') ?? '—' }}</td></tr>
                    <tr><th>Department</th><td>{{ optional($entity->department)->name ?? '—' }}</td></tr>
                    <tr><th>Organizations</th><td>{{ $entity->organizations->pluck('name')->implode(', ') ?: '—' }}</td></tr>
                    <tr><th>Reporting Manager</th><td>{{ optional($entity->reportingManager)->name ?? '—' }}</td></tr>
                    <tr><th>Address</th><td>{{ $entity->address ?? '—' }}</td></tr>
                    <tr><th>City</th><td>{{ $entity->city ?? '—' }}</td></tr>
                    <tr><th>State</th><td>{{ $entity->state ?? '—' }}</td></tr>
                    <tr><th>Pincode</th><td>{{ $entity->pincode ?? '—' }}</td></tr>
                    <tr>
                        <th>Photo</th>
                        <td>
                            @if($entity->photo)
                                <img src="{{ asset($entity->photo) }}" alt="User Photo" style="max-height: 100px;">
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Other Documents</th>
                        <td>
                            @if($entity->userDocuments->isNotEmpty())
                                @foreach($entity->userDocuments as $document)
                                    <div>
                                        <a href="{{ asset($document->file_path) }}" target="_blank">{{ $document->file_name }}</a>
                                    </div>
                                @endforeach
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr><th>Status</th><td>{{ $displayStatusLabel ?? 'Not Available' }}</td></tr>
                    <tr><th>Notes</th><td>{!! $entity->status_notes ?: '—' !!}</td></tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
{{-- <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content"> --}}
            {{-- @if (Route::has('masterapp.users.password.update'))
            <form method="POST"
                action="{{ route('masterapp.users.password.update', $entity->id) }}">
        @endif --}}

            {{-- <form method="POST"
                  action="{{ route('masterapp.users.password.update', $entity->id) }}" id="updatepassword">
                @csrf
                @method('PATCH')
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
                        <div class="input-group">
                            <input type="password" id="password"
                                   name="password"
                                   class="form-control"
                                   autocomplete="new-password"
                                   required>
                            <div class="input-group-append">
                                <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div id="modal-password-requirements" class="mt-2" style="display: none;">
                            <small>Password must contain:</small>
                            <ul class="list-unstyled small">
                                <li id="modal-req-length"><i class="fas fa-times text-danger"></i> At least 8 characters</li>
                                <li id="modal-req-uppercase"><i class="fas fa-times text-danger"></i> At least one uppercase letter</li>
                                <li id="modal-req-lowercase"><i class="fas fa-times text-danger"></i> At least one lowercase letter</li>
                                <li id="modal-req-number"><i class="fas fa-times text-danger"></i> At least one number</li>
                                <li id="modal-req-special"><i class="fas fa-times text-danger"></i> At least one special character (@$!%*?&)</li>
                            </ul>
                        </div>

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
</div> --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" >
        {{ session('success') }}
    </div>
@endif
@push('scripts')
<script>
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
    $('#updatepassword').validate({
        submitHandler: function (form) {
            const $form = $(form);
            const $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                type: 'PATCH',
                data: $form.serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function (res) {
                    $('#passwordModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message
                    });
                    setTimeout(() => {
                        window.location.href = "{{ route('masterapp.entity.info', ['type' => 'users', 'id' => $entity->id]) }}";
                    }, 5000);
                },

                error: function (xhr) {
                    $btn.prop('disabled', false);

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, (_, arr) => {
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
            password: {
                required: true,
                minlength: 8
            },
        },

        messages: {
            password: {
                required: "Please provide a password",
                minlength: "Password must be at least 8 characters"
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

    // Password strength validation for modal
    $('#password').on('input', function() {
        const password = $(this).val();
        if (password.length > 0) {
            $('#modal-password-requirements').show();
        } else {
            $('#modal-password-requirements').hide();
        }
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[@$!%*?&]/.test(password)
        };

        $('#modal-req-length i').toggleClass('fa-times text-danger', !requirements.length).toggleClass('fa-check text-success', requirements.length);
        $('#modal-req-uppercase i').toggleClass('fa-times text-danger', !requirements.uppercase).toggleClass('fa-check text-success', requirements.uppercase);
        $('#modal-req-lowercase i').toggleClass('fa-times text-danger', !requirements.lowercase).toggleClass('fa-check text-success', requirements.lowercase);
        $('#modal-req-number i').toggleClass('fa-times text-danger', !requirements.number).toggleClass('fa-check text-success', requirements.number);
        $('#modal-req-special i').toggleClass('fa-times text-danger', !requirements.special).toggleClass('fa-check text-success', requirements.special);
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
