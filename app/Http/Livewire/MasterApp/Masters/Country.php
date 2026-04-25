<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Country as CountryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Country extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $name = '';
    public ?string $code = '';
    public bool $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        $nameRule = Rule::unique('countries', 'name');
        $codeRule = Rule::unique('countries', 'code');

        if ($this->editId) {
            $nameRule->ignore($this->editId);
            $codeRule->ignore($this->editId);
        }

        return [
            'name' => ['required', 'string', 'max:75', $nameRule],
            'code' => ['nullable', 'string', 'max:5', $codeRule],
            'status' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'Name',
        'code' => 'Code',
        'status' => 'Status',
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
        if (! $this->canEditRecord()) {
            $this->dispatch('formResult', type: 'error', message: 'You are not authorized to edit this record.');
            return;
        }

        $record = CountryModel::findOrFail($id);

        $this->editId = $id;
        $this->name = $record->name;
        $this->code = $record->code ?? '';
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

        CountryModel::create([
            'name' => $this->name,
            'code' => $this->code ?: null,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Country created successfully.');
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

        $record = CountryModel::findOrFail((int) $this->editId);
        $record->update([
            'name' => $this->name,
            'code' => $this->code ?: null,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Country updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        if (! $this->canDeleteRecord()) {
            $this->dispatch('deleteResult', success: false, message: 'You are not authorized to delete this record.');
            return;
        }

        $record = CountryModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $hasStates = DB::table('states')->where('country_id', $id)->exists();
        if ($hasStates) {
            $this->dispatch(
                'deleteResult',
                success: false,
                message: 'This country cannot be deleted because states are mapped to it.'
            );
            return;
        }

        $record->delete();

        $this->closeModals();
        session()->flash('message', 'Country deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Country deleted successfully.');
    }

    public function getItemsProperty()
    {
        $query = CountryModel::query()
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                });
            });

        $allowedSorts = ['id', 'name', 'code', 'status'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortField, $sortDirection)
            ->get();
    }

    public function getViewRecordProperty(): ?CountryModel
    {
        if (! $this->showViewModal || $this->viewId === null) {
            return null;
        }

        return CountryModel::find($this->viewId);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.country', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->code = '';
        $this->status = true;
        $this->resetValidation();
    }

    private function canEditRecord(): bool
    {
        return (bool) (auth()->user()?->can('edit-country') ?? false);
    }

    private function canDeleteRecord(): bool
    {
        return (bool) (auth()->user()?->can('delete-country') ?? false);
    }
}
