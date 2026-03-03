<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Core\Season\Services\SeasonService;
use App\Models\Season as SeasonModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Seasons extends Component
{
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;
    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public string $description = '';
    public bool $status = true;

    public string $search = '';
    public string $statusFilter = '';

    private SeasonService $seasonService;

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('seasons', 'name')->whereNull('deleted_at');
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }
        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:65535'],
            'status' => ['boolean'],
        ];
    }

    protected $validationAttributes = [
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
    ];

    public function boot(SeasonService $seasonService): void
    {
        $this->seasonService = $seasonService;
    }

    public function getItemsProperty(): LengthAwarePaginator
    {
        return $this->seasonService->list(
            $this->search,
            $this->statusFilter,
            'created_at',
            'desc',
            15,
            request()->integer('page', 1)
        );
    }

    public function getViewRecordProperty(): ?SeasonModel
    {
        if (!$this->showViewModal || $this->viewId === null) {
            return null;
        }
        return $this->seasonService->getForView($this->viewId);
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
        $record = $this->seasonService->findWithTrashed($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->description = $record->description ?? '';
        $this->status = (bool) $record->status;
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
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');

            throw $e;
        }

        $this->seasonService->create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Season created.');
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

        $this->seasonService->update($this->editId, [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Season updated.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = $this->seasonService->findWithTrashed($id);
        $newStatus = !$record->status;
        $this->seasonService->update($id, ['status' => $newStatus]);
        $this->dispatch('statusUpdated', active: $newStatus, message: 'Status updated.');
    }

    /**
     * Delete by id (used with SweetAlert from front-end).
     */
    public function deleteById(int $id): void
    {
        try {
            $this->seasonService->find($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');

            return;
        }
        $this->seasonService->delete($id);
        $this->closeModals();
        session()->flash('message', 'Season deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Season deleted successfully.');
    }

    public function render()
    {
        return view('masterapp.livewire.masters.seasons', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->status = true;
        $this->resetValidation();
    }
}
