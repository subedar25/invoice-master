<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ request()->routeIs('masterapp.*') ? route('masterapp.profile.update') : route('profile.update') }}">
    @csrf
    @method('patch')

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

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile updated successfully.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="col-lg-10 bordar">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                    <input id="first_name" name="first_name" type="text" class="form-control" value="{{ old('first_name', $user->first_name) }}" required autofocus autocomplete="given-name">
                    @error('first_name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input id="last_name" name="last_name" type="text" class="form-control" value="{{ old('last_name', $user->last_name) }}" required autocomplete="family-name">
                    @error('last_name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="123-456-7890" maxlength="12" pattern="\d{3}-\d{3}-\d{4}">
                    <small class="text-muted">Use US format: 123-456-7890</small>
                    @error('phone')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                    <small class="text-muted">Email cannot be changed from profile.</small>
                </div>
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="form-group">
                <p class="mb-2 text-sm text-muted">Your email address is unverified.</p>
                <button form="send-verification" class="btn btn-link p-0">
                    Click here to re-send the verification email.
                </button>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-success small">A new verification link has been sent to your email address.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="card-footer">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
