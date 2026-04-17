<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Country as CountryModel;
use App\Models\Location as LocationModel;
use App\Models\State as StateModel;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Location extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public ?string $address = '';
    public string $country_id = '';
    public string $organization_id = '';
    public ?string $organizationFilter = '';
    public ?string $country = '';
    public ?string $state = '';
    public ?string $city = '';
    public ?string $postal_code = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organizationFilter = (string) $selectedOrganizationId;
            $this->organization_id = (string) $selectedOrganizationId;
        }
    }

    protected function rules(): array
    {
        $nameRule = Rule::unique('locations', 'name')->whereNull('deleted_at');
        if ($this->editId) {
            $nameRule->ignore($this->editId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $nameRule],
            'address' => ['nullable', 'string', 'max:1000'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'Name',
        'address' => 'Address',
        'country_id' => 'Country',
        'organization_id' => 'Organization',
        'state' => 'State',
        'city' => 'City',
        'postal_code' => 'PIN Code',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCountryId($value): void
    {
        if (! $value) {
            $this->country = null;
            $this->state = null;
            return;
        }

        $country = CountryModel::find((int) $value);
        $this->country = $country?->name;
        $this->state = null;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->showViewModal = false;
    }

    public function openEditModal(int $id): void
    {
        $record = LocationModel::withTrashed()->findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();

        $this->editId = $id;
        $this->name = $record->name;
        $this->address = $record->address ?? '';
        $this->country = $record->country ?? '';
        $this->state = $record->state ?? '';
        $this->city = $record->city ?? '';
        $this->postal_code = $record->postal_code ?? '';
        $this->organization_id = (string) ($selectedOrganizationId ?: $record->organization_id);

        $this->country_id = (string) (CountryModel::query()->where('name', $this->country)->value('id') ?? '');

        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->showViewModal = false;
        $this->viewId = null;
    }

    public function openViewModal(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
        $this->showCreateModal = false;
        $this->showEditModal = false;
    }

    public function backFromForm(): void
    {
        $this->closeModals();
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->editId = null;
        $this->viewId = null;
        $this->resetForm();
    }

    public function saveCreate(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }

        LocationModel::create([
            'name' => $this->name,
            'address' => $this->address ?: null,
            'country' => $this->country ?: null,
            'state' => $this->state ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postal_code ?: null,
            'organization_id' => $this->organization_id ?: null,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Location created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }

        $record = LocationModel::withTrashed()->findOrFail((int) $this->editId);
        $record->update([
            'name' => $this->name,
            'address' => $this->address ?: null,
            'country' => $this->country ?: null,
            'state' => $this->state ?: null,
            'city' => $this->city ?: null,
            'postal_code' => $this->postal_code ?: null,
            'organization_id' => $this->organization_id ?: null,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Location updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        $record = LocationModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $record->delete();
        $this->closeModals();
        session()->flash('message', 'Location deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Location deleted successfully.');
    }

    public function getItemsProperty()
    {
        return LocationModel::query()
            ->with('organization')
            ->when(filled($this->organizationFilter), function ($q) {
                $q->where('organization_id', $this->organizationFilter);
            })
            ->when($this->search, function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('city', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function getViewRecordProperty(): ?LocationModel
    {
        if (! $this->showViewModal || $this->viewId === null) {
            return null;
        }

        return LocationModel::withTrashed()->with('organization')->find($this->viewId);
    }

    public function getCountryOptionsProperty()
    {
        return CountryModel::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getStateOptionsProperty()
    {
        if (! $this->country_id) {
            return collect();
        }

        return StateModel::query()
            ->where('status', 1)
            ->where('country_id', (int) $this->country_id)
            ->orderBy('name')
            ->get(['name']);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.location', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->name = '';
        $this->address = '';
        $this->country_id = '';
        $this->organization_id = $selectedOrganizationId !== null ? (string) $selectedOrganizationId : '';
        $this->country = '';
        $this->state = '';
        $this->city = '';
        $this->postal_code = '';
        $this->resetValidation();
    }

    private function resolveSelectedOrganizationId(): ?int
    {
        $organizationId = session('current_organization_id');
        if (! empty($organizationId)) {
            return (int) $organizationId;
        }

        $fallback = auth()->user()?->last_selected_organization_id;
        return ! empty($fallback) ? (int) $fallback : null;
    }
}
