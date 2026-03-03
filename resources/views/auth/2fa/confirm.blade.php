<x-guest-layout>

    <form method="post" action="{{ route('login') }}">
        @csrf
        <p class="text-center">
            {{ trans('two-factor::messages.continue') }}
        </p>
        <div class="form-row justify-content-center py-3">
            <div>
                <x-input-label for="{{ $input }}" :value="__('Code')" />
                <x-text-input id="{{ $input }}" class="block mt-1 w-full" type="text" name="{{ $input }}" required autofocus/>
                <x-input-error :messages="$errors->get($input)" class="mt-2" />
            </div>

            @if(config('two-factor.safe_devices.enabled'))
            <div class="block mt-4">
                <label for="safe_device" class="inline-flex items-center">
                    <input id="safe_device" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="safe_device" value="1">
                    <span class="ms-2 text-sm text-gray-600">{{ trans('two-factor::messages.safe_device') }}</span>
                </label>
            </div>
            @endif

            <div class="w-100"></div>
            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Verify') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>