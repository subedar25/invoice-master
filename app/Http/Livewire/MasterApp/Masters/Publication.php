<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Core\Publication\Services\PublicationService;
use App\Models\Publication as PublicationModel;
use App\Models\PublicationType as PublicationTypeModel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Publication extends Component
{
    use WithPagination;

    protected PublicationService $publicationService;

    public function boot(PublicationService $publicationService): void
    {
        $this->publicationService = $publicationService;
    }

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showViewModal = false;

    public $editId = null;
    public $viewId = null;

    public $name = '';
    public $code = '';
    public $publication_type_id = '';
    public $description = '';
    public bool $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        $table = PublicationTypeModel::make()->getTable();
        $uniqueRule = Rule::unique('publications', 'name')->whereNull('deleted_at');
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }
        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'publication_type_id' => ['required', Rule::exists($table, 'id')],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }

    protected $validationAttributes = [
        'name' => 'Name',
        'publication_type_id' => 'Publication Type',
        'description' => 'Description',
        'status' => 'Active',
    ];

    protected function prepareForValidation($attributes): array
    {
        if (array_key_exists('publication_type_id', $attributes)) {
            $val = $attributes['publication_type_id'];
            if ($val === '' || $val === null || $val === 0 || $val === '0') {
                $attributes['publication_type_id'] = null;
            }
        }
        return $attributes;
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

    protected function loadEditRecord(int $id): void
    {
        $record = $this->publicationService->findWithTrashed($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->code = $record->code ?? $this->generateCodeFromName($record->name);
        $this->publication_type_id = $record->publication_type_id ?? '';
        $this->description = $record->description ?? '';
        $this->status = (bool) $record->status;
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
        $this->showViewModal = false;
        $this->loadEditRecord($id);
        $this->showEditModal = true;
    }

    public function openViewModal(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
        $this->showCreateModal = false;
        $this->showEditModal = false;
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

    public function backFromForm(): void
    {
        $this->closeModals();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->publication_type_id = '';
        $this->description = '';
        $this->status = true;
        $this->resetValidation();
    }

    public function saveCreate(): void
    {
        $this->normalizePublicationTypeId();
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }
        $this->publicationService->create([
            'name' => $this->name,
            'code' => $this->generateCodeFromName($this->name),
            'publication_type_id' => $this->publication_type_id ?: null,
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);
        $this->closeModals();
        session()->flash('message', 'Publication created successfully.');
        $this->dispatch('formResult', type: 'success', message: 'Publication created successfully.');
    }

    public function saveEdit(): void
    {
        $this->normalizePublicationTypeId();
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }
        $this->publicationService->update($this->editId, [
            'name' => $this->name,
            'publication_type_id' => $this->publication_type_id ?: null,
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);
        $this->closeModals();
        session()->flash('message', 'Publication updated successfully.');
        $this->dispatch('formResult', type: 'success', message: 'Publication updated successfully.');
    }

    protected function normalizePublicationTypeId(): void
    {
        $pid = $this->publication_type_id;
        if ($pid === '' || $pid === null || $pid === 0 || $pid === '0') {
            $this->publication_type_id = null;
        }
    }

    public function toggleStatus(int $id): void
    {
        $record = $this->publicationService->findWithTrashed($id);
        $newStatus = !$record->status;
        $this->publicationService->update($id, ['status' => $newStatus]);
        $this->dispatch('statusUpdated', active: $newStatus, message: 'Status updated.');
    }

    public function deleteById(int $id): void
    {
        try {
            $this->publicationService->find($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }
        $this->publicationService->delete($id);
        $this->closeModals();
        session()->flash('message', 'Publication deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Publication deleted successfully.');
    }

    public function getPublicationTypeOptionsProperty()
    {
        return $this->publicationService->getPublicationTypeOptions();
    }

    public function getViewRecordProperty(): ?PublicationModel
    {
        if (!$this->viewId) {
            return null;
        }
        return $this->publicationService->getForView($this->viewId);
    }

    public function render()
    {
        $items = $this->publicationService->list(
            $this->search,
            $this->sortField,
            $this->sortDirection,
            15,
            $this->getPage()
        );

        return view('masterapp.livewire.masters.publication', [
            'items' => $items,
        ]);
    }
}
