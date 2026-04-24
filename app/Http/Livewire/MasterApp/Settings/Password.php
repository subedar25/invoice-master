<?php

namespace App\Http\Livewire\MasterApp\Settings;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Password extends Component
{
    public function render(): View
    {
        return view('masterapp.livewire.settings.password', [
            'user' => auth()->user(),
        ]);
    }
}
