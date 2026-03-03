@extends('masterapp.emails.layouts.base')

@section('content')
    @php
        $displayName = $userName ?? 'there';
        $requesterName = $request->user ? trim($request->user->first_name . ' ' . $request->user->last_name) : 'A user';
        $startFormatted = $request->start_time ? $request->start_time->format('F j, Y g:i A') : '—';
        $endFormatted = $request->end_time ? $request->end_time->format('F j, Y g:i A') : '—';
        $statusLabel = ucfirst(str_replace('_', ' ', $request->status));
        $paidLabel = $request->paid ? 'Yes' : 'No';
    @endphp

    <p style="margin: 0 0 12px 0;">Hi {{ $displayName }},</p>

    <p style="margin: 0 0 12px 0;">
        A new time off request has been submitted and may require your attention.
    </p>

    <div class="divider"></div>

    <p style="margin: 0 0 8px 0;"><strong>Requester:</strong> {{ $requesterName }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Start time:</strong> {{ $startFormatted }}</p>
    <p style="margin: 0 0 8px 0;"><strong>End time:</strong> {{ $endFormatted }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Paid:</strong> {{ $paidLabel }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Status:</strong> {{ $statusLabel }}</p>
    @if(!empty($request->notes))
        <p style="margin: 0 0 8px 0;"><strong>Notes:</strong> {{ $request->notes }}</p>
    @endif

    <div class="divider"></div>

    <p style="margin: 0 0 16px 0;">
        Please log in to the portal to review and take action on this request if needed.
    </p>

    @if(!empty($portalUrl))
        <a href="{{ $portalUrl }}" class="button">View Time Off Requests</a>
    @endif
@endsection
