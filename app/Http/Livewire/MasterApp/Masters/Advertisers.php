<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Core\Advertiser\Services\AdvertiserService;
use App\Models\Advertiser as AdvertiserModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Advertisers extends Component
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

    private AdvertiserService $advertiserService;

    protected function rules(): array
    {
        // Unique among non-deleted only: allows reusing a name from a soft-deleted advertiser
        $uniqueRule = Rule::unique('advertisers', 'name')->whereNull('deleted_at');
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }
        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => [$this->editId ? 'nullable' : 'required', 'string', 'max:65535'],
            'status' => ['boolean'],
        ];
    }

    protected $validationAttributes = [
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Active',
    ];

    public function boot(AdvertiserService $advertiserService): void
    {
        $this->advertiserService = $advertiserService;
    }

    public function getItemsProperty(): LengthAwarePaginator
    {
        return $this->advertiserService->list(
            $this->search,
            $this->statusFilter,
            'created_at',
            'desc',
            15,
            request()->integer('page', 1)
        );
    }

    public function getViewRecordProperty(): ?AdvertiserModel
    {
        if (!$this->showViewModal || $this->viewId === null) {
            return null;
        }
        return $this->advertiserService->getForView($this->viewId);
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
        $record = $this->advertiserService->findWithTrashed($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->description = $record->description ?? '';
        $this->status = (bool) $record->active;
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

        $this->advertiserService->create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Advertiser created.');
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

        $this->advertiserService->update($this->editId, [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => $this->status,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Advertiser updated.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = $this->advertiserService->findWithTrashed($id);
        $newStatus = !$record->active;
        $this->advertiserService->update($id, ['status' => $newStatus]);
        $this->dispatch('statusUpdated', active: $newStatus, message: 'Status updated.');
    }

    /**
     * Delete by id (used with SweetAlert from front-end).
     * Soft delete; prevented if advertiser is in use.
     */
    public function deleteById(int $id): void
    {
        try {
            $record = $this->advertiserService->find($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');

            return;
        }
        if ($record->isInUse()) {
            $this->dispatch('deleteResult', success: false, message: 'This advertiser cannot be deleted because it is already in use.');

            return;
        }
        $this->advertiserService->delete($id);
        $this->closeModals();
        session()->flash('message', 'Advertiser deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Advertiser deleted successfully.');
    }

    public function render()
    {
        return view('masterapp.livewire.masters.advertisers', [
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
