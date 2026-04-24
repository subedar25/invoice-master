<?php

namespace App\Http\Livewire\MasterApp\Masters;

use Livewire\Component;

class Menu extends Component
{
    public $active = 'department';

    private function isSystemUser(): bool
    {
        return (auth()->user()?->user_type ?? '') === 'systemuser';
    }

    public function mount(): void
    {
        // Default to the first master tab the user is allowed to see
        if (auth()->user()?->can('department')) {
            $this->active = 'department';
        } elseif (auth()->user()?->can('list-organization') && $this->isSystemUser()) {
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

        $requestedTab = (string) request()->query('tab', '');
        if ($requestedTab !== '' && $this->canAccessTab($requestedTab)) {
            $this->active = $requestedTab;
        }
    }

    public function setActive(string $menu): void
    {
        if (! $this->canAccessTab($menu)) {
            return;
        }

        $this->active = $menu;
    }

    private function canAccessTab(string $tab): bool
    {
        return match ($tab) {
            'department' => (bool) auth()->user()?->can('department'),
            'organization' => (bool) auth()->user()?->can('list-organization') && $this->isSystemUser(),
            'country' => (bool) auth()->user()?->can('country'),
            'state' => (bool) auth()->user()?->can('state'),
            'location' => (bool) auth()->user()?->can('locations'),
            'vendor', 'vendor-category' => (bool) auth()->user()?->can('vendors'),
            'outlet' => (bool) auth()->user()?->can('outlets'),
            'product' => (bool) auth()->user()?->can('products'),
            'tax' => (bool) auth()->user()?->can('taxes'),
            'designation' => (bool) auth()->user()?->can('designation'),
            default => false,
        };
    }

    public function render()
    {
        return view('masterapp.livewire.masters.menu');
    }
}
