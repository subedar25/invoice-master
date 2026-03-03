<?php

namespace App\Http\Livewire\MasterApp\Masters;

use Livewire\Component;

class Menu extends Component
{
    public $active = 'organization-type';

    public function mount(): void
    {
        // Default to the first master tab the user is allowed to see
        if (auth()->user()?->can('organization_type')) {
            $this->active = 'organization-type';
        } elseif (auth()->user()?->can('seasons')) {
            $this->active = 'seasons';
        } elseif (auth()->user()?->can('advertisers')) {
            $this->active = 'advertisers';
        }
    }

    public function setActive(string $menu): void
    {
        $this->active = $menu;
    }

    public function render()
    {
        return view('masterapp.livewire.masters.menu');
    }
}
