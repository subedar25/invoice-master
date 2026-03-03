@extends('masterapp.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Masters</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <livewire:master-app.masters.menu />
            </div>
        </div>
    </div>
</section>


<style>
.settings-menu { border-right: 1px solid #eee; }
.settings-menu .nav-link { color: #444; padding: 6px 0; }
.settings-menu .nav-link.active { font-weight: 600; color: #000; }
</style>

@endsection
