<?php

namespace App\Http\Livewire\MasterApp\Masters;

use Livewire\Component;

class Menu extends Component
{
    public $active = 'department';

    public function mount(): void
    {
        // Default to the first master tab the user is allowed to see
        if (auth()->user()?->can('department')) {
            $this->active = 'department';
        } elseif (auth()->user()?->can('list-organization')) {
            $this->active = 'organization';
        } elseif (auth()->user()?->can('country')) {
            $this->active = 'country';
        } elseif (auth()->user()?->can('state')) {
            $this->active = 'state';
        } elseif (auth()->user()?->can('locations')) {
            $this->active = 'location';
        } elseif (auth()->user()?->can('vendors')) {
            $this->active = 'vendor';
        } elseif (auth()->user()?->can('outlets')) {
            $this->active = 'outlet';
        } elseif (auth()->user()?->can('products')) {
            $this->active = 'product';
        } elseif (auth()->user()?->can('taxes')) {
            $this->active = 'tax';
        } elseif (auth()->user()?->can('designation')) {
            $this->active = 'designation';
        } elseif (auth()->user()?->can('seasons')) {
            $this->active = 'seasons';
        } elseif (auth()->user()?->can('organization_type')) {
            $this->active = 'organization-type';
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
