@extends('masterapp.layouts.app')
@section('title', 'Profile')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary shadow-md">
                <div class="card-header">
                    <h3 class="card-title">Profile</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const $phone = $('#phone');

    function formatUsPhone(value) {
        const digits = (value || '').replace(/\D/g, '').slice(0, 10);
        if (digits.length <= 3) {
            return digits;
        }
        if (digits.length <= 6) {
            return digits.slice(0, 3) + '-' + digits.slice(3);
        }
        return digits.slice(0, 3) + '-' + digits.slice(3, 6) + '-' + digits.slice(6);
    }

    $phone.on('input', function() {
        const formatted = formatUsPhone($(this).val());
        $(this).val(formatted);
    });

    if ($phone.length) {
        $phone.val(formatUsPhone($phone.val()));
    }
});
</script>
@endpush
