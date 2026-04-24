<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Outlet as OutletModel;
use App\Models\Location as LocationModel;
use App\Models\User;
use App\Models\Country as CountryModel;
use App\Models\State as StateModel;
use App\Core\File\Services\FileManagementService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Outlet extends Component
{
    use WithPagination, WithFileUploads;

    protected FileManagementService $fileService;

    public function boot(FileManagementService $fileService): void
    {
        $this->fileService = $fileService;
        Gate::authorize('outlets');
    }

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public ?string $organizationFilter = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    // Form fields
    public string $name = '';
    public string $organization_id = '';
    public string $location_id = '';
    public string $area_manager_id = '';
    public ?string $address = '';
    public string $country_id = '';
    public string $state_id = '';
    public ?string $city = '';
    public ?string $pincode = '';
    public $photo;
    public ?string $existingPhoto = null;
    public bool $photoRemoved = false;
    public bool $status = true;

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
        return [
            'name' => ['required', 'string', 'max:255'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'area_manager_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereNull('user_type')
                        ->orWhere('user_type', '<>', 'systemuser');
                }),
            ],
            'address' => ['nullable', 'string', 'max:1000'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        $record = OutletModel::withTrashed()->findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->editId = $id;
        $this->name = $record->name;
        $this->organization_id = (string) ($selectedOrganizationId ?: $record->organization_id);
        $this->location_id = (string)$record->location_id;
        $amId = $record->area_manager_id;
        if ($amId) {
            $manager = User::query()->find($amId);
            $this->area_manager_id = ($manager && ($manager->user_type ?? '') === 'systemuser')
                ? ''
                : (string) $amId;
        } else {
            $this->area_manager_id = '';
        }
        $this->address = $record->address;
        $this->country_id = (string)$record->country_id;
        $this->state_id = (string)$record->state_id;
        $this->city = $record->city;
        $this->pincode = $record->pincode;
        $this->existingPhoto = $record->photo;
        $this->status = (bool)$record->status;
        
        $this->showEditModal = true;
    }

    public function openViewModal(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
    }

    public function removePhoto(): void
    {
        $this->photo = null;
        $this->existingPhoto = null;
        $this->photoRemoved = true;
    }

    public function saveCreate(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        $this->validate();

        $photoPath = $this->photo ? $this->fileService->upload($this->photo, 'outlets') : null;

        OutletModel::create([
            'name' => $this->name,
            'organization_id' => $this->organization_id,
            'location_id' => $this->location_id,
            'area_manager_id' => $this->area_manager_id ?: null,
            'address' => $this->address,
            'country_id' => $this->country_id ?: null,
            'state_id' => $this->state_id ?: null,
            'city' => $this->city,
            'pincode' => $this->pincode,
            'photo' => $photoPath,
            'status' => $this->status,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Outlet created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        $this->validate();
        $record = OutletModel::withTrashed()->findOrFail($this->editId);
        
        $data = [
            'name' => $this->name,
            'organization_id' => $this->organization_id,
            'location_id' => $this->location_id,
            'area_manager_id' => $this->area_manager_id ?: null,
            'address' => $this->address,
            'country_id' => $this->country_id ?: null,
            'state_id' => $this->state_id ?: null,
            'city' => $this->city,
            'pincode' => $this->pincode,
            'status' => $this->status,
        ];

        if ($this->photo) {
            $this->fileService->delete($record->photo);
            $data['photo'] = $this->fileService->upload($this->photo, 'outlets');
        } elseif ($this->photoRemoved) {
            $this->fileService->delete($record->photo);
            $data['photo'] = null;
        }

        $record->update($data);
        $this->dispatch('formResult', type: 'success', message: 'Outlet updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        $record = OutletModel::find($id);
        if ($record) {
            $this->fileService->delete($record->photo);
            $record->delete();
            $this->dispatch('deleteResult', success: true, message: 'Outlet deleted successfully.');
        }
    }

    public function restoreById(int $id): void
    {
        if (! $this->isSystemUser()) {
            $this->dispatch('deleteResult', success: false, message: 'Only system user can revert deleted records.');
            return;
        }

        $record = OutletModel::withTrashed()->find($id);
        if (! $record || ! $record->trashed()) {
            $this->dispatch('deleteResult', success: false, message: 'Deleted record not found.');
            return;
        }

        $record->restore();
        $this->dispatch('deleteResult', success: true, message: 'Outlet reverted successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $record = OutletModel::findOrFail($id);
        $record->status = !$record->status;
        $record->save();
        $this->dispatch('statusUpdated', active: $record->status, message: 'Status updated.');
    }

    public function closeModals(): void
    {
        $this->showCreateModal = $this->showEditModal = $this->showViewModal = false;
        $this->resetForm();
    }

    public function getItemsProperty()
    {
        return OutletModel::query()
            ->with(['location.organization', 'areaManager'])
            ->when($this->isSystemUser(), function ($q) {
                $q->withTrashed();
            })
            ->when(filled($this->organizationFilter), function ($q) {
                $q->where('organization_id', $this->organizationFilter);
            })
            ->when($this->search, function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('city', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function getLocationOptionsProperty()
    {
        return LocationModel::query()
            ->when(filled($this->organization_id), function ($query) {
                $query->where('organization_id', (int) $this->organization_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getAreaManagerOptionsProperty()
    {
        return User::query()
            ->where(function ($q) {
                $q->whereNull('user_type')
                    ->orWhere('user_type', '<>', 'systemuser');
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    public function getCountryOptionsProperty() { return CountryModel::orderBy('name')->get(['id', 'name']); }
    
    public function getStateOptionsProperty() {
        return $this->country_id ? StateModel::where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name']) : collect();
    }

    public function getViewRecordProperty()
    {
        return $this->viewId ? OutletModel::withTrashed()->with(['location.organization', 'areaManager', 'state', 'country'])->find($this->viewId) : null;
    }

    public function render()
    {
        return view('masterapp.livewire.masters.outlet', ['items' => $this->items]);
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->name = $this->location_id = $this->area_manager_id = '';
        $this->organization_id = $selectedOrganizationId !== null ? (string) $selectedOrganizationId : '';
        $this->address = $this->city = $this->pincode = '';
        $this->country_id = $this->state_id = '';
        $this->photo = $this->existingPhoto = null;
        $this->photoRemoved = false;
        $this->status = true;
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

    private function isSystemUser(): bool
    {
        return (auth()->user()?->user_type ?? '') === 'systemuser';
    }
}