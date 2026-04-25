@extends('masterapp.layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $usersUrl = route('masterapp.users.index');
    $vendorsUrl = route('masterapp.masters', ['tab' => 'vendor']);
    $outletsUrl = route('masterapp.masters', ['tab' => 'outlet']);
    $invoiceUrl = route('invoice.index');
    $dashboardVisibility = $dashboardVisibility ?? [];
@endphp
<div class="row mt-5 justify-content-center dashboard-stats-row">
    @if(!empty($dashboardVisibility['total_invoice']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $invoiceUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--indigo">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['total_invoice'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Total Invoice</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-file-invoice"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['pending_invoice']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $invoiceUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--red">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['pending_invoice'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Pending Invoice</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['in_process_invoice']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $invoiceUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--amber">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['in_process_invoice'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">In Process Invoice</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-spinner"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['approved_invoice']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $invoiceUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--sky">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['approved_invoice'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Approved Invoice</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-thumbs-up"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['outlets']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $outletsUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--teal">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['outlets'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Outlets</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-store"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['vendors']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $vendorsUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--slate">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['vendors'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Vendors</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-truck"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['accountant']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $usersUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--orange">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['accountant'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Accountant</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-calculator"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['area_manager']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $usersUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--green">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['area_manager'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Area Manager</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-user-cog"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['general_manager']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $usersUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--violet">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['general_manager'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">General Managers</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-user"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['operational_general_manager']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $usersUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--blue">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['operational_general_manager'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Operational General Managers</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-user-tie"></i></div>
        </div>
        </a>
    </div>
    @endif

    @if(!empty($dashboardVisibility['completed_invoice']))
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-10 mb-3">
        <a href="{{ $invoiceUrl }}" class="dashboard-stat-card-link">
        <div class="dashboard-stat-card dashboard-stat-card--emerald">
            <div class="dashboard-stat-card__content">
                <h2 class="dashboard-stat-card__count mb-1">{{ (int) ($dashboardCounts['completed_invoice'] ?? 0) }}</h2>
                <div class="dashboard-stat-card__label">Completed Invoice</div>
            </div>
            <div class="dashboard-stat-card__icon"><i class="fas fa-check-circle"></i></div>
        </div>
        </a>
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
    .dashboard-stats-row {
        max-width: 1180px;
        margin-left: auto;
        margin-right: auto;
    }

    .dashboard-stat-card {
        border-radius: 14px;
        min-height: 148px;
        padding: 1.3rem 1.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #fff;
        box-shadow: 0 10px 24px rgba(16, 24, 40, 0.14);
    }

    .dashboard-stat-card-link {
        display: block;
        color: inherit;
        text-decoration: none !important;
    }

    .dashboard-stat-card-link:hover .dashboard-stat-card,
    .dashboard-stat-card-link:focus .dashboard-stat-card {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(16, 24, 40, 0.18);
    }

    .dashboard-stat-card--blue {
        background: linear-gradient(135deg, #53c5ff 0%, #279dff 100%);
    }

    .dashboard-stat-card--violet {
        background: linear-gradient(135deg, #8f7bff 0%, #5f67ff 100%);
    }

    .dashboard-stat-card--green {
        background: linear-gradient(135deg, #4fd58a 0%, #1fbf74 100%);
    }

    .dashboard-stat-card--orange {
        background: linear-gradient(135deg, #ffb367 0%, #ff8a3d 100%);
    }

    .dashboard-stat-card--teal {
        background: linear-gradient(135deg, #5ed8d0 0%, #2cb6b0 100%);
    }

    .dashboard-stat-card--slate {
        background: linear-gradient(135deg, #7f93b0 0%, #5f738f 100%);
    }

    .dashboard-stat-card--indigo {
        background: linear-gradient(135deg, #7b8cff 0%, #4f63f7 100%);
    }

    .dashboard-stat-card--emerald {
        background: linear-gradient(135deg, #43d39e 0%, #1faa74 100%);
    }

    .dashboard-stat-card--red {
        background: linear-gradient(135deg, #ff7b89 0%, #ef4f63 100%);
    }

    .dashboard-stat-card--amber {
        background: linear-gradient(135deg, #ffc46b 0%, #ff9b42 100%);
    }

    .dashboard-stat-card--sky {
        background: linear-gradient(135deg, #67ccff 0%, #3a9eff 100%);
    }

    .dashboard-stat-card__count {
        font-size: 2.15rem;
        line-height: 1;
        font-weight: 700;
        color: #ffffff;
    }

    .dashboard-stat-card__label {
        font-size: 1.22rem;
        line-height: 1.25;
        font-weight: 500;
        letter-spacing: 0.1px;
        max-width: 80%;
    }

    .dashboard-stat-card__icon {
        font-size: 3rem;
        line-height: 1;
        color: rgba(255, 255, 255, 0.48);
        margin-left: 0.8rem;
        flex-shrink: 0;
    }
</style>
@endpush
