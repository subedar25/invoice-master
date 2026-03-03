@extends('layouts.custom-admin')

@section('title', 'Drop Point Details', 'bold')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="bg-white shadow rounded p-6 mb-6">
        <h2 class="text-xl font-semibold">{{ $dropPoint->name }}</h2>
        <p class="text-gray-500 text-sm mt-1">dropPoint details and permissions</p>

        <div class="mt-4">
            <h3 class="text-sm font-medium text-gray-700">Permissions</h3>
            <div class="flex flex-wrap gap-2 mt-2">
               
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Change History</h3>
            <a href="#" onclick="openModelHistory('{{ class_basename(\App\Models\dropPoint::class) }}','{{ $dropPoint->id }}'); return false;" class="text-indigo-600 text-sm">Load History</a>
        </div>

        <div id="inlineModelHistory">
      

      
        </div>
    </div>

</div>
@endsection