<form method="post" action="{{ request()->routeIs('masterapp.*') ? route('masterapp.profile.password.update') : route('profile.password.update') }}">
    @csrf
    @method('put')

    @php
        $currentPasswordError = $errors->updatePassword->first('current_password') ?: $errors->first('current_password');
        $passwordError = $errors->updatePassword->first('password') ?: $errors->first('password');
        $passwordConfirmationError = $errors->updatePassword->first('password_confirmation') ?: $errors->first('password_confirmation');
        $updatePasswordErrors = $errors->updatePassword->all();
        $defaultErrors = $errors->all();
        $allPasswordErrors = !empty($updatePasswordErrors) ? $updatePasswordErrors : $defaultErrors;
    @endphp

    @if (!empty($allPasswordErrors))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($allPasswordErrors as $message)
                <div>{{ $message }}</div>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Password updated successfully.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="col-lg-10 bordar">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="update_password_current_password">Current Password <span class="text-danger">*</span></label>
                    <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
                    @if ($currentPasswordError)
                        <span class="text-danger small">{{ $currentPasswordError }}</span>
                    @endif
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group" style="position:relative;">
                    <label for="update_password_password">New Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
                        <div class="input-group-append">
                            <span class="input-group-text" id="toggleChangePassword" style="cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <small class="text-muted d-block">Use at least 8 characters with A-Z, a-z, 0-9, and one special character.</small>
                    <div id="change-password-requirements" class="mt-2" style="display:none; position:absolute; left:15px; right:15px; top:100%; z-index:1050; background:#fff; border:1px solid #ced4da; border-radius:.375rem; padding:8px 10px; box-shadow:0 6px 18px rgba(0,0,0,.12);">
                        <small>Password must contain:</small>
                        <ul class="list-unstyled small mb-0">
                            <li id="change-req-length"><i class="fas fa-times text-danger"></i> At least 8 characters</li>
                            <li id="change-req-uppercase"><i class="fas fa-times text-danger"></i> At least one uppercase letter</li>
                            <li id="change-req-lowercase"><i class="fas fa-times text-danger"></i> At least one lowercase letter</li>
                            <li id="change-req-number"><i class="fas fa-times text-danger"></i> At least one number</li>
                            <li id="change-req-special"><i class="fas fa-times text-danger"></i> At least one special character</li>
                        </ul>
                    </div>
                    @if ($passwordError)
                        <span class="text-danger small d-block">{{ $passwordError }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="update_password_password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                        <div class="input-group-append">
                            <span class="input-group-text" id="toggleChangeConfirmPassword" style="cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <span id="changePasswordMatchMessage" class="small"></span>
                    @if ($passwordConfirmationError)
                        <span class="text-danger small">{{ $passwordConfirmationError }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="pt-3 mt-3 d-flex flex-wrap align-items-center">
        <a href="{{ $cancelUrl ?? url()->previous() }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary ml-2">Submit</button>
    </div>
</form>
