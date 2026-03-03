@extends('masterapp.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Activity Logs</h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-end add-new">
                <button type="button" class="btn btn-default ml-2" id="toggleFilterBtn">
                    <i class="fa fa-filter"></i> Filter
                </button>
                &nbsp;
                <a href="{{ route('masterapp.audit.export', request()->query()) }}" class="btn btn-success">
                    <i class="fa fa-file-excel"></i> Export
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div id="filterWrapper" class="card mb-3" style="display:none;">
            <div class="card-body">
                <form method="GET" action="{{ route('masterapp.audit.index') }}">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Event</label>
                            <select name="event" class="form-control">
                                <option value="">All</option>
                                @foreach($events as $ev)
                                    <option value="{{ $ev }}" {{ request('event') == $ev ? 'selected' : '' }}>
                                        {{ ucfirst($ev) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Model</label>
                            <select name="auditable_type" class="form-control">
                                <option value="">All</option>
                                @foreach($auditableTypes as $type)
                                    <option value="{{ $type }}" {{ request('auditable_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label class="font-weight-bold d-block mb-1">User</label>
                            <select id="filter_user_id" name="user_id" class="form-control select2-filter-user" style="height: 40px;">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Date from &rarr; to</label>
                            <div class="d-flex">
                                <input type="text" name="date_from" id="audit_date_from"
                                       value="{{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('m/d/Y') : '' }}"
                                       placeholder="mm/dd/yyyy" class="form-control mr-2" autocomplete="off">
                                <input type="text" name="date_to" id="audit_date_to"
                                       value="{{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('m/d/Y') : '' }}"
                                       placeholder="mm/dd/yyyy" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="form-row align-items-end">
                        <div class="form-group col-md-9">
                            <label>Search</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Search..."
                                   class="form-control">
                        </div>
                        <div class="form-group col-md-3 d-flex">
                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('masterapp.audit.index') }}" class="btn btn-default">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>When</th>
                                    <th>User</th>
                                    <th>Events</th>
                                    <th>Model</th>
                                    <th>Activity</th>
                                    <th>Meta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($audits as $audit)
                                    <tr>
                                        <td class="text-center">{{ $audit->id }}</td>
                                        <td class="text-center">
                                            {{ $audit->created_at->diffForHumans() }}<br>
                                            <small class="text-muted">{{ $audit->created_at->format('m/d/Y h:i A') }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($audit->user)
                                                <div class="font-weight-bold">{{ $audit->user->name }}</div>
                                                <small class="text-muted">ID: {{ $audit->user->id }}</small>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge
                                                {{ $audit->event == 'created' ? 'badge-success' :
                                                   ($audit->event == 'deleted' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucfirst($audit->event) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="font-weight-bold">{{ class_basename($audit->auditable_type) }}</div>
                                            <small class="text-muted">ID: {{ $audit->auditable_id }}</small>
                                        </td>
                                        <td class="text-sm">
                                            @if($audit->old_values)
                                                <strong>Old:</strong>
                                                <pre class="bg-light p-2 rounded">{{ json_encode($audit->old_values, JSON_PRETTY_PRINT) }}</pre>
                                            @endif

                                            @if($audit->new_values)
                                                <strong>New:</strong>
                                                <pre class="bg-light p-2 rounded">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary"
                                                onclick="openModelHistory('{{ addslashes(class_basename($audit->auditable_type)) }}', '{{ $audit->auditable_id }}')">
                                                Model History
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No audit records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-end">
                            {{ $audits->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="modelHistoryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHistoryTitle">Model History</h5>
                <button type="button" class="close" id="closeModelHistory" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modelHistoryContent">
                <div class="text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#toggleFilterBtn').on('click', function() {
        $('#filterWrapper').slideToggle();
    });


    $(function() {
        if ($('#filter_user_id').length && typeof $.fn.select2 !== 'undefined') {
            $('#filter_user_id').select2({
                width: '100%',
                placeholder: 'All Users',
                allowClear: true
            });
        }
    });


    (function() {
        var auditDateOpts = { dateFormat: 'm/d/Y', allowInput: true };
        if (typeof flatpickr !== 'undefined') {
            if ($('#audit_date_from').length) flatpickr('#audit_date_from', auditDateOpts);
            if ($('#audit_date_to').length) flatpickr('#audit_date_to', auditDateOpts);
        }
    })();


    function openModelHistory(modelType, modelId) {
        const title = document.getElementById('modelHistoryTitle');
        const content = document.getElementById('modelHistoryContent');

        title.innerText = modelType + " History";
        content.innerHTML = "<div class='text-muted'>Loading...</div>";

        $('#modelHistoryModal').modal('show');

        fetch(`/master-app/audit/history?auditable_type=${modelType}&auditable_id=${modelId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error("Page not found");
            return res.text();
        })
        .then(html => content.innerHTML = html)
        .catch(() => content.innerHTML = "<div class='text-danger'>Error loading history.</div>");
    }

    document.getElementById("closeModelHistory").onclick = () =>
        $('#modelHistoryModal').modal('hide');
</script>
@endpush
@endsection
