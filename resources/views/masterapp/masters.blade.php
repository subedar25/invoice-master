@extends('masterapp.layouts.app')

@section('title', 'Masters')

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
.settings-menu {
    border-right: 1px solid #eee;
    position: relative;
    z-index: 5;
    pointer-events: auto;
}
.settings-content {
    position: relative;
    z-index: 1;
}
.settings-menu .nav-item {
    position: relative;
    z-index: 6;
}
.settings-menu .nav-link {
    color: #444;
    padding: 6px 0;
    cursor: pointer;
    pointer-events: auto;
    position: relative;
    z-index: 7;
    -webkit-appearance: none;
    appearance: none;
}
.settings-menu .nav-link.active { font-weight: 600; color: #000; }
</style>

@endsection
