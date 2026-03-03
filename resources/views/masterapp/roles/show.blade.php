@extends('layouts.custom-admin')

@section('title', 'Role Details', 'bold')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="bg-white shadow rounded p-6 mb-6">
        <h2 class="text-xl font-semibold">{{ $role->name }}</h2>
        <p class="text-gray-500 text-sm mt-1">Role details and permissions</p>

        <div class="mt-4">
            <h3 class="text-sm font-medium text-gray-700">Permissions</h3>
            <div class="flex flex-wrap gap-2 mt-2">
                @foreach($role->permissions as $p)
                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700">{{ $p->display_name ?? $p->name }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Change History</h3>
            <a href="#" onclick="openModelHistory('{{ class_basename(\App\Models\Role::class) }}','{{ $role->id }}'); return false;" class="text-indigo-600 text-sm">Load History</a>
        </div>

        <div id="inlineModelHistory">
            {{-- Optionally load inline by calling controller or including partial with preloaded audits --}}
            @php
            $audits = \OwenIt\Auditing\Models\Audit::where('auditable_type', \App\Models\Role::class)
            ->where('auditable_id', $role->id)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->limit(10)
            ->get();
            @endphp

            @include('audits.partials.model-history', ['audits' => $audits])
        </div>
    </div>

</div>
@endsection