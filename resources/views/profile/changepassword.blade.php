@extends('masterapp.layouts.app')
@section('title', 'Change Password')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary shadow-md">
                <div class="card-header">
                    <h3 class="card-title">Change Password</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const $password = $('#update_password_password');
    const $confirm = $('#update_password_password_confirmation');
    const $requirements = $('#change-password-requirements');
    const $matchMessage = $('#changePasswordMatchMessage');
    const $togglePassword = $('#toggleChangePassword');
    const $toggleConfirmPassword = $('#toggleChangeConfirmPassword');

    function toggleReqIcon(id, isValid) {
        const $icon = $(id + ' i');
        $icon.toggleClass('fa-times text-danger', !isValid)
             .toggleClass('fa-check text-success', isValid);
    }

    function validatePasswordRequirements() {
        const password = $password.val() || '';
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{}|\\:;"'<>,.?/~`]/.test(password)
        };

        if (password.length > 0) {
            $requirements.show();
        } else {
            $requirements.hide();
        }

        toggleReqIcon('#change-req-length', requirements.length);
        toggleReqIcon('#change-req-uppercase', requirements.uppercase);
        toggleReqIcon('#change-req-lowercase', requirements.lowercase);
        toggleReqIcon('#change-req-number', requirements.number);
        toggleReqIcon('#change-req-special', requirements.special);
    }

    function validatePasswordMatch() {
        const password = $password.val() || '';
        const confirm = $confirm.val() || '';

        if (password && confirm) {
            if (password === confirm) {
                $matchMessage.text('Passwords match')
                    .removeClass('text-danger')
                    .addClass('text-success');
            } else {
                $matchMessage.text('Passwords do not match')
                    .removeClass('text-success')
                    .addClass('text-danger');
            }
        } else {
            $matchMessage.text('').removeClass('text-success text-danger');
        }
    }

    $password.on('input', function() {
        validatePasswordRequirements();
        validatePasswordMatch();
    });

    $password.on('focus', function() {
        if (($password.val() || '').length > 0) {
            $requirements.show();
        }
    });

    $password.on('blur', function() {
        setTimeout(function() {
            $requirements.hide();
        }, 120);
    });

    $confirm.on('input', validatePasswordMatch);

    $togglePassword.on('click', function() {
        const $icon = $(this).find('i');
        if ($password.attr('type') === 'password') {
            $password.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $password.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $toggleConfirmPassword.on('click', function() {
        const $icon = $(this).find('i');
        if ($confirm.attr('type') === 'password') {
            $confirm.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $confirm.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>
@endpush
