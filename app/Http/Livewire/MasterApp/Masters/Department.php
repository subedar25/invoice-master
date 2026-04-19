<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Department as DepartmentModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Department extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public string $description = '';
    public string $parent_id = '';
    public string $organization_id = '';
    public string $organizationFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function boot(): void
    {
        Gate::authorize('department');
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
        $uniqueRule = Rule::unique('departments', 'name')->whereNull('deleted_at');
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:65535'],
            'parent_id' => ['nullable', 'exists:departments,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'Name',
        'description' => 'Description',
        'parent_id' => 'Parent Department',
        'organization_id' => 'Organization',
    ];

    public function updatingSearch(): void
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
        $this->resetForm();
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->showViewModal = false;
    }

    public function openEditModal(int $id): void
    {
        $record = DepartmentModel::withTrashed()->findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();

        $this->editId = $id;
        $this->name = $record->name;
        $this->description = $record->description ?? '';
        $this->parent_id = (string) ($record->parent_id ?? '');
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

    public function openEditFromView(int $id): void
    {
        $this->showViewModal = false;
        $this->viewId = null;
        $this->openEditModal($id);
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

        DepartmentModel::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'organization_id' => $this->organization_id ?: null,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Department created successfully.');
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

        $record = DepartmentModel::withTrashed()->findOrFail((int) $this->editId);
        $record->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'organization_id' => $this->organization_id ?: null,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Department updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        $record = DepartmentModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $hasChildren = DepartmentModel::where('parent_id', $id)->exists();
        $hasUsers = $record->users()->exists();

        if ($hasChildren || $hasUsers) {
            $this->dispatch(
                'deleteResult',
                success: false,
                message: 'This department cannot be deleted because it is in use.'
            );
            return;
        }

        $record->delete();

        $this->closeModals();
        session()->flash('message', 'Department deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Department deleted successfully.');
    }

    public function getItemsProperty()
    {
        $query = DepartmentModel::query()
            ->with(['parent', 'organization'])
            ->when($this->organizationFilter !== '', function ($q) {
                $q->where('organization_id', (int) $this->organizationFilter);
            })
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });

        $allowedSorts = ['name', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortField, $sortDirection)
            ->paginate(15, ['*'], 'page', request()->integer('page', 1));
    }

    public function getViewRecordProperty(): ?DepartmentModel
    {
        if (! $this->showViewModal || $this->viewId === null) {
            return null;
        }

        return DepartmentModel::withTrashed()
            ->with([
                'parent',
                'organization',
                'users',
                'children' => fn ($q) => $q->orderBy('created_at', 'desc'),
            ])
            ->find($this->viewId);
    }

    public function getParentOptionsProperty()
    {
        $query = DepartmentModel::query()->orderBy('name');
        if ($this->organization_id !== '') {
            $query->where('organization_id', (int) $this->organization_id);
        }

        if ($this->editId) {
            $query->where('id', '!=', $this->editId);
        }

        return $query->get();
    }

    public function render()
    {
        return view('masterapp.livewire.masters.department', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->name = '';
        $this->description = '';
        $this->parent_id = '';
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
