@extends('masterapp.emails.layouts.base')

@section('content')
    @php
        $displayName = $userName ?? 'there';
        $startFormatted = $request->start_time ? $request->start_time->format('F j, Y g:i A') : '—';
        $endFormatted = $request->end_time ? $request->end_time->format('F j, Y g:i A') : '—';
        $newStatusLabel = ucfirst(str_replace('_', ' ', $newStatus ?? $request->status));
        $oldStatusLabel = isset($oldStatus) ? ucfirst(str_replace('_', ' ', $oldStatus)) : null;
        $changedByName = $changedByUser ? trim($changedByUser->first_name . ' ' . $changedByUser->last_name) : 'An administrator';
    @endphp

    <p style="margin: 0 0 12px 0;">Hi {{ $displayName }},</p>

    <p style="margin: 0 0 12px 0;">
        Your time off request has been updated.
    </p>

    <div class="divider"></div>

    <p style="margin: 0 0 8px 0;"><strong>Period:</strong> {{ $startFormatted }} – {{ $endFormatted }}</p>
    @if($oldStatusLabel)
        <p style="margin: 0 0 8px 0;"><strong>Previous status:</strong> {{ $oldStatusLabel }}</p>
    @endif
    <p style="margin: 0 0 8px 0;"><strong>New status:</strong> {{ $newStatusLabel }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Updated by:</strong> {{ $changedByName }}</p>
    @if(!empty($adminNotes))
        <p style="margin: 0 0 8px 0;"><strong>Notes:</strong> {{ $adminNotes }}</p>
    @endif

    <div class="divider"></div>

    <p style="margin: 0 0 16px 0;">
        Please log in to the portal to view full details of your request.
    </p>

    @if(!empty($portalUrl))
        <a href="{{ $portalUrl }}" class="button">View My Time Off Requests</a>
    @endif
@endsection
