<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Two-Factor Authentication</h4>
    </div>

    <div class="card-body">

        {{-- 2FA NOT ENABLED --}}
        @if(! $enabled)

            {{-- Initial State --}}
            @if(! $showSetup)
                <p class="text-muted">
                    Add an extra layer of security to your account using an authenticator app.
                </p>

                <button wire:click="startSetup" class="btn btn-primary mt-3">
                    Setup
                </button>
            @else
                {{-- Setup Screen --}}
                <p class="text-muted">
                    Scan this QR code with Google Authenticator or any TOTP app.
                </p>

                <div class="text-center my-3">
                    {!! $qr !!}
                </div>

                <p class="text-left">
                    Secret Key: <strong>{{ $secret }}</strong>
                </p>

                <form wire:submit.prevent="confirm" class="mt-3">
                    <div class="form-group">
                        <input type="text" wire:model.defer="code" class="form-control" placeholder="Enter 6-digit OTP">
                        @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <button class="btn btn-success mt-2">Confirm & Activate</button>
                    <button type="button" wire:click="$set('showSetup', false)" class="btn btn-secondary mt-2">
                        Cancel
                    </button>
                </form>
            @endif

        {{-- 2FA ENABLED --}}
        @else

            <div class="alert alert-success">
                Two-factor authentication is active on your account.
            </div>

            <p class="text-muted">
            Store these codes somewhere safe. Each code can be used once if you lose access to your authenticator.
        </p>

        <ul class="list-group mb-3" id="recovery-codes-list">
            @foreach ($recoveryCodes as $item)
                <li class="">
                    <code>{{ $item['code'] }}</code>
                </li>
            @endforeach
        </ul>

        <button class="btn btn-primary" onclick="copyAllCodes()">Copy All Codes</button>

        <button wire:click="regenerate" class="btn btn-warning">
                Regenerate Recovery Codes
            </button>

            <button wire:click="askDisable" class="btn btn-danger ms-2">
                Disable 2FA
            </button>

            @if($confirmingDisable)
                <div class="card mt-3 p-3 border-danger">
                    <h5>Confirm Password to Disable 2FA</h5>

                    <input type="text" style="display:none">
                    <input type="password" style="display:none">

                    <input 
                        type="password"
                        wire:model.defer="password"
                        class="form-control"
                        placeholder="Enter password"
                        autocomplete="new-password"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                    />

                    @error('password') 
                        <span class="text-danger">{{ $message }}</span> 
                    @enderror

                    <div class="mt-2">
                        <button wire:click="confirmDisable" class="btn btn-danger">Confirm Disable</button>
                        <button wire:click="$set('confirmingDisable', false)" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            @endif

        @endif

    </div>
</div>



