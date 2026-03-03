@extends('masterapp.emails.layouts.base')

@section('content')
    @php
        $displayName = $userName ?? 'there';
    @endphp

    <p style="margin: 0 0 12px 0;">Hi {{ $displayName }},</p>

    @if(!empty($content))
        {!! $content !!}
    @else
        <p style="margin: 0 0 12px 0;">
            This is a test email from {{ $appName ?? config('app.name') }}.
            You can replace this message with any custom content you want to validate.
        </p>
        <div class="divider"></div>
        <p style="margin: 0 0 16px 0;">
            If you see the header, body, and footer clearly, your email template is ready.
        </p>
        @if(!empty($actionUrl) && !empty($actionText))
            <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
        @endif
    @endif
@endsection
