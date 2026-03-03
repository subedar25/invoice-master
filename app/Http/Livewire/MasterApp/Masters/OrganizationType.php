<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Core\OrganizationType\Services\OrganizationTypeService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class OrganizationType extends Component
{
    use WithPagination;

    protected OrganizationTypeService $organizationTypeService;

    public function boot(OrganizationTypeService $organizationTypeService): void
    {
        $this->organizationTypeService = $organizationTypeService;
    }

    public $search = '';
    public $statusFilter = ''; // '', '1' (Active), '0' (Inactive)
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;

    public $editId = null;
    public $viewId = null;
    public $deleteId = null;
    public $deleteInUseMessage = '';
    public $returnToViewId = null;

    public $name = '';
    public $code = '';
    public $description = '';
    public $parent_id = '';
    public $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected function rules()
    {
        $uniqueRule = 'unique:organization_types,name';
        if ($this->editId) {
            $uniqueRule .= ',' . $this->editId;
        }
        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:organization_types,id'],
            'status' => ['boolean'],
        ];
    }

    protected $validationAttributes = [
        'name' => 'Name',
        'description' => 'Description',
        'parent_id' => 'Parent',
        'status' => 'Status',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->returnToViewId = null;
        $this->resetForm();
        $this->showCreateModal = true;
    }

    /**
     * Open create form with parent pre-selected (from parent type view).
     */
    public function openCreateChildModal(int $parentId): void
    {
        $this->returnToViewId = $this->viewId;
        $this->resetForm();
        $this->parent_id = (string) $parentId;
        $this->showViewModal = false;
        $this->showCreateModal = true;
    }

    protected function loadEditRecord(int $id): void
    {
        $record = $this->organizationTypeService->findWithTrashed($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->code = $record->code ?? $this->generateCodeFromName($record->name);
        $this->description = $record->description ?? '';
        $this->parent_id = $record->parent_id ?? '';
        $this->status = (bool) $record->active;
    }

    /**
     * Code displayed in form: when creating, generated from name; when editing, existing code.
     */
    public function getDisplayCodeProperty(): string
    {
        if ($this->editId) {
            return $this->code;
        }
        return $this->generateCodeFromName($this->name);
    }

    protected function generateCodeFromName(string $name): string
    {
        return strtolower(Str::slug(trim($name), '_'));
    }

    public function openEditModal(int $id): void
    {
        $this->returnToViewId = null;
        $this->showViewModal = false;
        $this->loadEditRecord($id);
        $this->showEditModal = true;
    }

    public function openEditFromView(int $id): void
    {
        $this->returnToViewId = $this->viewId;
        $this->showViewModal = false;
        $this->loadEditRecord($id);
        $this->showEditModal = true;
    }

    public function openViewModal(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
    }

    public function openDeleteModal(int $id): void
    {
        $this->deleteId = $id;
        $this->deleteInUseMessage = '';
        $this->showDeleteModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->editId = $this->viewId = $this->deleteId = null;
        $this->deleteInUseMessage = '';
        $this->returnToViewId = null;
        // $this->dispatch('org-types:datatable');
    }

    public function backFromForm(): void
    {
        if ($this->showEditModal && $this->returnToViewId) {
            $this->showEditModal = false;
            $this->resetForm();
            $this->editId = null;
            $this->viewId = $this->returnToViewId;
            $this->returnToViewId = null;
            $this->showViewModal = true;
            return;
        }

        $this->closeModals();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->parent_id = '';
        $this->status = true;
        $this->resetValidation();
    }

    public function saveCreate(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');

            throw $e;
        }
        $this->organizationTypeService->create([
            'name' => $this->name,
            'code' => $this->generateCodeFromName($this->name),
            'description' => $this->description ?: null,
            'parent_id' => $this->parent_id ?: null,
            'status' => $this->status,
        ]);
        if ($this->returnToViewId) {
            $returnId = $this->returnToViewId;
            $this->showCreateModal = false;
            $this->resetForm();
            $this->returnToViewId = null;
            $this->viewId = $returnId;
            $this->showViewModal = true;
            session()->flash('message', 'Organization type created successfully.');
            $this->dispatch('formResult', type: 'success', message: 'Organization type created successfully.');
            return;
        }
        $this->closeModals();
        session()->flash('message', 'Organization type created successfully.');
        $this->dispatch('formResult', type: 'success', message: 'Organization type created successfully.');
    }

    public function saveEdit(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');

            throw $e;
        }
        $this->organizationTypeService->update($this->editId, [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'parent_id' => $this->parent_id ?: null,
            'status' => $this->status,
        ]);
        if ($this->returnToViewId) {
            $returnId = $this->returnToViewId;
            $this->showEditModal = false;
            $this->resetForm();
            $this->editId = null;
            $this->returnToViewId = null;
            $this->viewId = $returnId;
            $this->showViewModal = true;
            session()->flash('message', 'Organization type updated successfully.');
            $this->dispatch('formResult', type: 'success', message: 'Organization type updated successfully.');
            return;
        }

        $this->closeModals();
        session()->flash('message', 'Organization type updated successfully.');
        $this->dispatch('formResult', type: 'success', message: 'Organization type updated successfully.');
    }

    public function confirmDelete(): void
    {
        try {
            $record = $this->organizationTypeService->find($this->deleteId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->closeModals();
            return;
        }
        if ($record->isInUse()) {
            $this->deleteInUseMessage = 'This organization type cannot be deleted because it is in use (other types use it as parent).';
            return;
        }
        $this->organizationTypeService->delete($this->deleteId);
        $this->closeModals();
        session()->flash('message', 'Organization type deleted successfully.');
    }

    /**
     * Delete by id (used with SweetAlert from front-end).
     */
    public function deleteById(int $id): void
    {
        try {
            $record = $this->organizationTypeService->find($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');

            return;
        }
        if ($record->isInUse()) {
            $this->dispatch('deleteResult', success: false, message: 'This organization type cannot be deleted because it is in use (other types use it as parent).');

            return;
        }
        $this->organizationTypeService->delete($id);
        $this->closeModals();
        session()->flash('message', 'Organization type deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Organization type deleted successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $record = $this->organizationTypeService->findWithTrashed($id);
        $newStatus = ! $record->active;
        $this->organizationTypeService->update($id, [
            'status' => $newStatus,
        ]);
        $this->dispatch('statusUpdated', active: $newStatus, message: $newStatus ? 'Status set to Active' : 'Status set to Inactive');
    }

    /**
     * Parent options for dropdown: active and not soft-deleted; exclude self when editing.
     */
    public function getParentOptionsProperty()
    {
        return $this->organizationTypeService->getParentOptions($this->editId ?: null);
    }

    /**
     * Record for view modal (read-only).
     */
    public function getViewRecordProperty()
    {
        if (! $this->viewId) {
            return null;
        }
        return $this->organizationTypeService->getForView($this->viewId);
    }

    public function render()
    {
        $items = $this->organizationTypeService->list(
            $this->search,
            $this->statusFilter,
            $this->sortField,
            $this->sortDirection,
            15,
            $this->getPage()
        );

        return view('masterapp.livewire.masters.organization-type', [
            'items' => $items,
        ]);
    }
}
