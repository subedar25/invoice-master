@extends('masterapp.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Settings</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <livewire:master-app.settings.menu />
            </div>
        </div>
    </div>
</section>

<style>
.settings-menu { border-right: 1px solid #eee; }
.settings-menu .nav-link { color: #444; padding: 6px 0; }
.settings-menu .nav-link.active { font-weight: 600; color: #000; }
.twofa-code { display: inline-block; background: #e6f0ff; padding: 4px 8px; border-radius: 4px; font-weight: 600; letter-spacing: 1px; }
.qr-section img { width: 180px; height: 180px; border: 1px solid #ddd; padding: 8px; background: #fff; margin: 15px 0; }
</style>

<script>
function copyAllCodes() {
    let codes = [];

    document.querySelectorAll('#recovery-codes-list li').forEach(el => {
        codes.push(el.innerText);
    });

    navigator.clipboard.writeText(codes.join('\n')).then(() => {
        alert('All recovery codes copied!');
    });
}
</script>

@endsection
