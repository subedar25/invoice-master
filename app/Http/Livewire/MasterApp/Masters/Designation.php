<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\UserDesignation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Designation extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public bool $status = true;
    public string $organization_id = '';
    public string $organizationFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function boot(): void
    {
        Gate::authorize('designation');
    }

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
        $uniqueRule = Rule::unique('user_designation', 'name')->where(
            fn ($query) => $query->where('organization_id', (int) $this->organization_id)
        );
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'status' => ['boolean'],
            'organization_id' => ['required', 'exists:organizations,id'],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'Designation Name',
        'status' => 'Status',
        'organization_id' => 'Organization',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
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
        if (! $this->canEditRecord()) {
            $this->dispatch('formResult', type: 'error', message: 'You are not authorized to edit this record.');
            return;
        }

        $record = UserDesignation::findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();

        $this->editId = $id;
        $this->name = $record->name;
        $this->status = (bool) $record->status;
        $this->organization_id = (string) ($selectedOrganizationId ?: $record->organization_id);

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

        UserDesignation::create([
            'organization_id' => (int) $this->organization_id,
            'name' => $this->name,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Designation created successfully.');
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

        $record = UserDesignation::findOrFail((int) $this->editId);
        $record->update([
            'organization_id' => (int) $this->organization_id,
            'name' => $this->name,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Designation updated successfully.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = UserDesignation::findOrFail($id);
        $record->status = ! $record->status;
        $record->save();

        $this->dispatch('statusUpdated', active: $record->status, message: 'Designation status updated.');
    }

    public function deleteById(int $id): void
    {
        if (! $this->canDeleteRecord()) {
            $this->dispatch('deleteResult', success: false, message: 'You are not authorized to delete this record.');
            return;
        }

        $record = UserDesignation::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $record->delete();
        $this->closeModals();
        session()->flash('message', 'Designation deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Designation deleted successfully.');
    }

    public function getItemsProperty()
    {
        $allowedSorts = ['id', 'name', 'status'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return UserDesignation::query()
            ->when($this->organizationFilter !== '', function ($q) {
                $q->where('organization_id', (int) $this->organizationFilter);
            })
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(15);
    }

    public function getViewRecordProperty(): ?UserDesignation
    {
        if (! $this->viewId) {
            return null;
        }

        return UserDesignation::with('organization')->find($this->viewId);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.designation', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->name = '';
        $this->status = true;
        $this->organization_id = $selectedOrganizationId !== null ? (string) $selectedOrganizationId : '';
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

    private function canEditRecord(): bool
    {
        return (bool) (auth()->user()?->can('edit-designation') ?? false);
    }

    private function canDeleteRecord(): bool
    {
        return (bool) (auth()->user()?->can('delete-designation') ?? false);
    }
}
