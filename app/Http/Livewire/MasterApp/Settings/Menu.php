<?php

namespace App\Http\Livewire\MasterApp\Settings;

use Livewire\Component;

class Menu extends Component
{
    public $active = 'profile'; // default active tab

    public function setActive($menu)
    {
        $this->active = $menu;
    }

    public function render()
    {
        return view('masterapp.livewire.settings.menu');
    }
}
