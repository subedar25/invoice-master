<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Organization as OrganizationModel;
use App\Core\File\Services\FileManagementService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Organization extends Component
{
    use WithPagination, WithFileUploads;

    protected FileManagementService $fileService;

    public function boot(FileManagementService $fileService): void
    {
        $this->fileService = $fileService;
    }

    public string $search = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public string $description = '';
    public string $invoice_prefix = '';
    public $logo;
    public ?string $existingLogo = null;
    public bool $logoRemoved = false;
    public bool $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('organizations', 'name')->whereNull('deleted_at');
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:65535'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'max:2048'],
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
        $this->showEditModal = false;
        $this->showViewModal = false;
    }

    public function openEditModal(int $id): void
    {
        $record = OrganizationModel::withTrashed()->findOrFail($id);

        $this->editId = $id;
        $this->name = $record->name;
        $this->description = $record->description ?? '';
        $this->invoice_prefix = $record->invoice_prefix ?? '';
        $this->existingLogo = $record->logo;
        $this->logo = null;
        $this->logoRemoved = false;
        $this->status = (bool) ($record->status ?? true);

        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->showViewModal = false;
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

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->existingLogo = null;
        $this->logoRemoved = true;
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

        $logoPath = null;
        if ($this->logo) {
            $logoPath = $this->fileService->upload($this->logo, 'organization');
        }

        OrganizationModel::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'invoice_prefix' => $this->invoice_prefix ?: null,
            'logo' => $logoPath,
            'status' => $this->status,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Organization created successfully.');
        $this->closeModals();
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

        $record = OrganizationModel::withTrashed()->findOrFail((int) $this->editId);
        
        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'invoice_prefix' => $this->invoice_prefix ?: null,
            'status' => $this->status,
        ];

        if ($this->logo) {
            if ($record->logo) {
                $this->fileService->delete($record->logo);
            }
            $data['logo'] = $this->fileService->upload($this->logo, 'organization');
        } elseif ($this->logoRemoved) {
            if ($record->logo) {
                $this->fileService->delete($record->logo);
            }
            $data['logo'] = null;
        }

        $record->update($data);

        $this->dispatch('formResult', type: 'success', message: 'Organization updated successfully.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = OrganizationModel::findOrFail($id);
        $record->status = !$record->status;
        $record->save();

        $this->dispatch('statusUpdated', active: $record->status, message: 'Organization status updated.');
    }

    public function deleteById(int $id): void
    {
        $record = OrganizationModel::find($id);
        if ($record) {
            $record->delete();
            $this->dispatch('deleteResult', success: true, message: 'Organization deleted successfully.');
        }
    }

    public function render()
    {
        $query = OrganizationModel::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });

        return view('masterapp.livewire.masters.organization', [
            'items' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(15),
        ]);
    }

    private function resetForm(): void
    {
        $this->name = $this->description = $this->invoice_prefix = '';
        $this->logo = null;
        $this->existingLogo = null;
        $this->logoRemoved = false;
        $this->status = true;
        $this->resetValidation();
    }
}