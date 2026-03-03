{{-- @extends('layouts.app') --}}



<div id="user-calendar"
     data-user="{{ $entity->id }}">
</div>

@push('scripts')
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script src="{{ asset('js/user-calendar.js') }}"></script>
@endpush
{{-- @include('entity.tabs.users.timesheet.modal') --}}
@include('partials.generic-model')

