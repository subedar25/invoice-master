<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\VendorCategory as VendorCategoryModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class VendorCategory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $organizationFilter = '';
    public string $organization_id = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public string $desc = '';
    public bool $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function boot(): void
    {
        Gate::authorize('vendors');
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
        $uniqueRule = Rule::unique('vendor_categories', 'name')
            ->where(function ($query) {
                $query->whereNull('deleted_at');
                if ($this->organization_id !== '') {
                    $query->where('organization_id', (int) $this->organization_id);
                }
            });

        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'desc' => ['nullable', 'string', 'max:65535'],
            'status' => ['boolean'],
            'organization_id' => ['required', 'exists:organizations,id'],
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
        $this->showEditModal = false;
        $this->showViewModal = false;
    }

    public function openEditModal(int $id): void
    {
        $record = VendorCategoryModel::findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();

        $this->editId = $id;
        $this->name = $record->name;
        $this->desc = $record->desc ?? '';
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

        VendorCategoryModel::create([
            'organization_id' => (int) $this->organization_id,
            'name' => $this->name,
            'desc' => $this->desc ?: null,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Vendor category created successfully.');
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

        $record = VendorCategoryModel::findOrFail((int) $this->editId);
        $record->update([
            'organization_id' => (int) $this->organization_id,
            'name' => $this->name,
            'desc' => $this->desc ?: null,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Vendor category updated successfully.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = VendorCategoryModel::findOrFail($id);
        $record->status = ! $record->status;
        $record->save();

        $this->dispatch('statusUpdated', active: $record->status, message: 'Vendor category status updated.');
    }

    public function deleteById(int $id): void
    {
        $record = VendorCategoryModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        if ($record->vendors()->exists()) {
            $this->dispatch(
                'deleteResult',
                success: false,
                message: 'This category cannot be deleted because vendors are linked to it.'
            );
            return;
        }

        $record->delete();
        $this->closeModals();
        $this->dispatch('deleteResult', success: true, message: 'Vendor category deleted successfully.');
    }

    public function getItemsProperty()
    {
        return VendorCategoryModel::query()
            ->with('organization')
            ->when($this->organizationFilter !== '', function ($q) {
                $q->where('organization_id', (int) $this->organizationFilter);
            })
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('desc', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function getViewRecordProperty(): ?VendorCategoryModel
    {
        if (! $this->viewId) {
            return null;
        }

        return VendorCategoryModel::with('organization')->find($this->viewId);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.vendor-category', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->name = '';
        $this->desc = '';
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
}

