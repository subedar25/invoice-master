@extends('layouts.auth')
@section('title', 'Reset Password - ' . config('app.name'))

@section('content')
<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="form-group">
        <label for="email">Email <span class="text-danger">*</span></label>
        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        @error('email')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group" style="position:relative;">
        <label for="reset_password">New Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <input id="reset_password" name="password" type="password" class="form-control" required autocomplete="new-password">
            <div class="input-group-append">
                <span class="input-group-text" id="toggleResetPassword" style="cursor: pointer;">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>
        <small class="text-muted d-block">Use at least 8 characters with A-Z, a-z, 0-9, and one special character.</small>
        <div id="reset-password-requirements" class="mt-2" style="display:none; position:absolute; left:0; right:0; top:100%; z-index:1050; background:#fff; border:1px solid #ced4da; border-radius:.375rem; padding:8px 10px; box-shadow:0 6px 18px rgba(0,0,0,.12);">
            <small>Password must contain:</small>
            <ul class="list-unstyled small mb-0">
                <li id="reset-req-length"><i class="fas fa-times text-danger"></i> At least 8 characters</li>
                <li id="reset-req-uppercase"><i class="fas fa-times text-danger"></i> At least one uppercase letter</li>
                <li id="reset-req-lowercase"><i class="fas fa-times text-danger"></i> At least one lowercase letter</li>
                <li id="reset-req-number"><i class="fas fa-times text-danger"></i> At least one number</li>
                <li id="reset-req-special"><i class="fas fa-times text-danger"></i> At least one special character</li>
            </ul>
        </div>
        @error('password')
            <span class="text-danger small d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="reset_password_confirmation">Confirm Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <input id="reset_password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
            <div class="input-group-append">
                <span class="input-group-text" id="toggleResetConfirmPassword" style="cursor: pointer;">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>
        <span id="resetPasswordMatchMessage" class="small"></span>
        @error('password_confirmation')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>

    <div class="row">
        <div class="col-12">
            <a href="{{ route('login') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const $password = $('#reset_password');
    const $confirm = $('#reset_password_confirmation');
    const $requirements = $('#reset-password-requirements');
    const $matchMessage = $('#resetPasswordMatchMessage');
    const $togglePassword = $('#toggleResetPassword');
    const $toggleConfirmPassword = $('#toggleResetConfirmPassword');

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

        toggleReqIcon('#reset-req-length', requirements.length);
        toggleReqIcon('#reset-req-uppercase', requirements.uppercase);
        toggleReqIcon('#reset-req-lowercase', requirements.lowercase);
        toggleReqIcon('#reset-req-number', requirements.number);
        toggleReqIcon('#reset-req-special', requirements.special);
    }

    function validatePasswordMatch() {
        const password = $password.val() || '';
        const confirmVal = $confirm.val() || '';

        if (password && confirmVal) {
            if (password === confirmVal) {
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
