@if($audits->count() == 0)
    <p class="text-center text-gray-500">No history found.</p>
@else
    <ul class="space-y-4">
        @foreach($audits as $audit)
            <li class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="mb-2">
                    <span class="font-semibold text-gray-700">Date:</span>
                    <span class="text-gray-600">{{ $audit->created_at->toDayDateTimeString() }}</span>
                </div>
                <div class="mb-2">
                    <span class="font-semibold text-gray-700">IP:</span>
                    <span class="text-gray-600">{{ $audit->ip_address ?? '—' }}</span>
                </div>
                <div class="mb-2">
                    <span class="font-semibold text-gray-700">URL:</span>
                    <span class="text-gray-600 break-all">{{ $audit->url ?? '—' }}</span>
                </div>
                @if($audit->old_values)
                    <div class="mb-2">
                        <span class="font-semibold text-gray-700">Old Values:</span>
                        <pre class="bg-white border rounded p-2 text-xs mt-1 whitespace-pre-wrap">
{{ json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </pre>
                    </div>
                @endif
                @if($audit->new_values)
                    <div>
                        <span class="font-semibold text-gray-700">New Values:</span>
                        <pre class="bg-white border rounded p-2 text-xs mt-1 whitespace-pre-wrap">
{{ json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </pre>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
