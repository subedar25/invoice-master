<div class="settings-password-content" style="overflow: visible;">
    <h4 class="mb-3">Change Password</h4>
    <p class="text-muted mb-4">Update your password. You will need your current password.</p>

    @include('profile.partials.update-password-form', ['cancelUrl' => route('masterapp.settings')])
</div>
