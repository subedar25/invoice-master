<div class="profile-settings">
    @if (session('profile-status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile updated successfully.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($editing)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Edit profile</h4>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="cancelEdit">Cancel</button>
        </div>

        <form wire:submit.prevent="save" class="border rounded p-3 bg-white">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="settings_first_name">First name <span class="text-danger">*</span></label>
                        <input id="settings_first_name" type="text" class="form-control @error('first_name') is-invalid @enderror"
                               wire:model.defer="first_name" autocomplete="given-name" required>
                        @error('first_name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="settings_last_name">Last name <span class="text-danger">*</span></label>
                        <input id="settings_last_name" type="text" class="form-control @error('last_name') is-invalid @enderror"
                               wire:model.defer="last_name" autocomplete="family-name" required>
                        @error('last_name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="settings_phone" class="d-block">Phone number</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text text-muted" title="Phone"><i class="fas fa-phone"></i></span>
                    </div>
                    <input id="settings_phone" type="tel" inputmode="numeric" autocomplete="tel"
                           class="form-control @error('phone') is-invalid @enderror"
                           wire:model.live="phone" placeholder="123-456-7890" maxlength="12">
                </div>
                @error('phone')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="settings_address">Address</label>
                <textarea id="settings_address" class="form-control @error('address') is-invalid @enderror"
                          wire:model.defer="address" rows="3" placeholder="Street, city, state, postal code"></textarea>
                @error('address')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mb-0">
                <label class="d-block font-weight-bold">Profile photo</label>
                <div class="profile-settings-photo-editor card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-auto text-center">
                                @php
                                    $newPreviewUrl = null;
                                    if ($photo) {
                                        try {
                                            $newPreviewUrl = $photo->temporaryUrl();
                                        } catch (\Throwable $e) {
                                            $newPreviewUrl = null;
                                        }
                                    }
                                @endphp
                                <div class="profile-settings-photo-preview mx-auto mb-2 mb-md-0">
                                    @if($newPreviewUrl)
                                        <img src="{{ $newPreviewUrl }}" alt="New photo preview" class="profile-settings-photo-img">
                                        <span class="profile-settings-photo-badge badge badge-info">New</span>
                                    @elseif($user->photo && ! $remove_photo)
                                        <img src="{{ asset($user->photo) }}" alt="Current photo" class="profile-settings-photo-img">
                                        <span class="profile-settings-photo-badge badge badge-secondary">Current</span>
                                    @else
                                        <div class="profile-settings-photo-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="profile-settings-photo-badge badge badge-light border text-muted">No photo</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col">
                                <p class="small text-muted mb-2 mb-md-3">
                                    Square images work best. JPG, PNG or GIF — max <strong>2&nbsp;MB</strong>.
                                </p>
                                @if($user->photo && ! $remove_photo)
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" class="custom-control-input" id="settings_remove_photo" wire:model="remove_photo">
                                        <label class="custom-control-label" for="settings_remove_photo">Remove current photo</label>
                                    </div>
                                @endif
                                <div class="custom-file profile-settings-custom-file">
                                    <input type="file" class="custom-file-input @error('photo') is-invalid @enderror" id="settings_photo_input" wire:model="photo" accept="image/*">
                                    <label class="custom-file-label text-truncate" for="settings_photo_input" data-browse="Browse">Choose image…</label>
                                </div>
                                @error('photo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <div wire:loading wire:target="photo" class="small text-primary mt-2">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Preparing upload…
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </form>
    @else
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h4 class="mb-0">Profile</h4>
            <button type="button" class="btn btn-sm btn-primary" wire:click="startEdit">
                <i class="fas fa-edit mr-1"></i> Edit
            </button>
        </div>

        <div class="row align-items-start">
            <div class="col-md-auto text-center mb-4 mb-md-0 pr-md-4">
                <div class="profile-settings-photo-view mx-auto">
                    @if($user->photo)
                        <img src="{{ asset($user->photo) }}" alt="Profile photo" class="profile-settings-photo-img">
                    @else
                        <div class="profile-settings-photo-placeholder profile-settings-photo-placeholder--large">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                <div class="small text-muted mt-2">Profile photo</div>
            </div>
            <div class="col">
                <dl class="row mb-0">
                    <dt class="col-sm-4">First name</dt>
                    <dd class="col-sm-8">{{ $user->first_name }}</dd>

                    <dt class="col-sm-4">Last name</dt>
                    <dd class="col-sm-8">{{ $user->last_name }}</dd>

                    <dt class="col-sm-4">Phone number</dt>
                    <dd class="col-sm-8">{{ $user->phone ?: '—' }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $user->email }}</dd>

                    <dt class="col-sm-4">Address</dt>
                    <dd class="col-sm-8">{!! $user->address ? nl2br(e($user->address)) : '—' !!}</dd>
                </dl>

                <hr class="my-3">

                <h6 class="text-muted mb-2">Reporting manager</h6>
                @if($user->reportingManager)
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8">{{ trim(($user->reportingManager->first_name ?? '').' '.($user->reportingManager->last_name ?? '')) }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $user->reportingManager->email ?? '—' }}</dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $user->reportingManager->phone ?? '—' }}</dd>

                        <dt class="col-sm-4">Designation</dt>
                        <dd class="col-sm-8">{{ $user->reportingManager->designation?->name ?? '—' }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">None assigned</p>
                @endif
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
.profile-settings .profile-settings-photo-view {
    width: 148px;
    height: 148px;
    position: relative;
}
.profile-settings .profile-settings-photo-preview {
    width: 132px;
    height: 132px;
    position: relative;
}
.profile-settings .profile-settings-photo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    display: block;
}
.profile-settings .profile-settings-photo-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(145deg, #e2e8f0 0%, #cbd5e1 100%);
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.25rem;
    border: 3px dashed #94a3b8;
    box-sizing: border-box;
}
.profile-settings .profile-settings-photo-placeholder--large {
    font-size: 3rem;
}
.profile-settings .profile-settings-photo-badge {
    position: absolute;
    bottom: 4px;
    right: 50%;
    transform: translateX(50%);
    white-space: nowrap;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    padding: 0.2rem 0.45rem;
    border-radius: 999px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}
.profile-settings .profile-settings-photo-editor {
    background: #fff;
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.5rem;
}
.profile-settings .profile-settings-custom-file .custom-file-label::after {
    content: "Browse";
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    function syncProfilePhotoLabel() {
        var $input = $('#settings_photo_input');
        if (!$input.length) {
            return;
        }
        var $label = $input.next('.custom-file-label');
        var file = $input[0].files && $input[0].files[0];
        $label.text(file ? file.name : 'Choose image…');
    }
    $(document).on('change', '#settings_photo_input', syncProfilePhotoLabel);
    document.addEventListener('livewire:init', function () {
        Livewire.hook('morph.updated', syncProfilePhotoLabel);
    });
})();
</script>
@endpush
