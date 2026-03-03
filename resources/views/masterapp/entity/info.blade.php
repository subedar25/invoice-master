@extends('masterapp.layouts.app')

@section('content')

    {{-- Entity Title --}}
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <h1 class="mb-1" style="font-size: 30px; font-weight: bold; text-indent: 20px;">
            {{ $entity->{$config['label_field']} }}
            <small class="text-muted">[{{ $config['title'] }}]</small>
        </h1>


        {{-- <div class="col-sm-6 text-right"> --}}
            {{-- As discussed QA team currently hide back button --}}
                {{-- <a href="{{ url()->previous() }}"
                    class="btn btn-secondary"
                    style="width:100px;">
                    <i class="fa fa-arrow-left mr-1"></i> Back
                </a> --}}
        {{-- </div> --}}
    </div>


    {{-- Entity Tabs --}}

    <ul class="nav nav-tabs justify-content-center">
        @foreach ($tabs as $tab)
            <li class="nav-item">
                <a
                    href="{{ route('masterapp.entity.info', [
                        'type' => $type,
                        'id'   => $entity->id,
                        'tab'  => $tab
                    ]) }}"
                    class="nav-link border-0 border-bottom border-3
                        {{ strtolower($tab) === $currentTab
                            ? 'active border-primary fw-semibold'
                            : 'border-transparent text-secondary'
                        }}"
                >
                    {{ ucwords($tab) }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Entity Content --}}
    <div class="card" style="margin-left: 26px;">
        <div class="card-body">
            @includeIf($tabViewName, [
            'entity' => $entity
        ])

        </div>
    </div>

@endsection

