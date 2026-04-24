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
@include('profile.partials.update-password-form-scripts')
@endpush
