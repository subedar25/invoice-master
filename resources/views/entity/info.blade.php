@extends('masterapp.layouts.app')

@section('content')

    {{-- Entity Title --}}
    <div class="mb-2">
        <h1 class="mb-1" style="font-size: 30px; font-weight: bold;">
            {{ $entity->{$config['label_field']} }}
            <small class="text-muted">[{{ $config['title'] }}]</small>
        </h1>
    </div>

 @php
    $currentTab = request()->query('tab', 'info');
@endphp
    {{-- Entity Tabs --}}

<ul class="nav nav-tabs justify-content-center">
    @foreach ($config['tabs'] as $tab)
        <li class="nav-item">
            <a
                href="{{ route('masterapp.entity.info', ['type' => $type, 'id' => $entity->id, 'tab' => $tab]) }}"
                class="
                    nav-link
                    border-0
                    border-bottom
                    border-3
                    {{ $currentTab === $tab ? 'active border-primary fw-semibold' : 'border-transparent text-secondary' }}
                "
                style="transition: border-color .25s ease, color .25s ease;">
                {{ ucwords(str_replace('_',' ', $tab)) }}
            </a>
        </li>
    @endforeach
</ul>

    {{-- Entity Content --}}
    <div class="card">
        <div class="card-body">
            @includeIf("entity.tabs.$type.$currentTab", [
                'entity' => $entity
            ])
        </div>
    </div>

@endsection

