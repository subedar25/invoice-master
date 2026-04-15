<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Tax as TaxModel;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Tax extends Component
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

    public string $tax_name = '';
    public string $tax_value = '';
    public bool $tax_status = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        $uniqueRule = Rule::unique('taxes', 'tax_name')->whereNull('deleted_at');
        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }

        return [
            'tax_name' => ['required', 'string', 'max:255', $uniqueRule],
            'tax_value' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_status' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'tax_name' => 'Tax Name',
        'tax_value' => 'Tax Value',
        'tax_status' => 'Status',
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
        $record = TaxModel::withTrashed()->findOrFail($id);

        $this->editId = $id;
        $this->tax_name = $record->tax_name;
        $this->tax_value = number_format((float) $record->tax_value, 2, '.', '');
        $this->tax_status = (bool) $record->tax_status;

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
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }

        TaxModel::create([
            'tax_name' => $this->tax_name,
            'tax_value' => $this->tax_value,
            'tax_status' => $this->tax_status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Tax created successfully.');
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

        $record = TaxModel::withTrashed()->findOrFail((int) $this->editId);
        $record->update([
            'tax_name' => $this->tax_name,
            'tax_value' => $this->tax_value,
            'tax_status' => $this->tax_status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Tax updated successfully.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = TaxModel::findOrFail($id);
        $record->tax_status = ! $record->tax_status;
        $record->save();

        $this->dispatch('statusUpdated', active: $record->tax_status, message: 'Tax status updated.');
    }

    public function deleteById(int $id): void
    {
        $record = TaxModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $record->delete();
        $this->closeModals();
        session()->flash('message', 'Tax deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Tax deleted successfully.');
    }

    public function getItemsProperty()
    {
        $allowedSorts = ['tax_name', 'tax_value', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return TaxModel::query()
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('tax_name', 'like', '%' . $search . '%')
                        ->orWhere('tax_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(15);
    }

    public function getViewRecordProperty(): ?TaxModel
    {
        if (! $this->viewId) {
            return null;
        }

        return TaxModel::withTrashed()->find($this->viewId);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.tax', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $this->tax_name = '';
        $this->tax_value = '';
        $this->tax_status = true;
        $this->resetValidation();
    }
}
