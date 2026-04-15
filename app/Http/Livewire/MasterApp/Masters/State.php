<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Country as CountryModel;
use App\Models\State as StateModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class State extends Component
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

    public string $countryFilter = '';
    public string $country_id = '';
    public string $name = '';
    public ?string $code = '';
    public bool $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'countryFilter' => ['except' => ''],
    ];

    protected function rules(): array
    {
        $nameRule = Rule::unique('states', 'name')->where(function ($query) {
            return $query->where('country_id', (int) $this->country_id);
        });

        if ($this->editId) {
            $nameRule->ignore($this->editId);
        }

        return [
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:100', $nameRule],
            'code' => ['nullable', 'string', 'max:10'],
            'status' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'country_id' => 'Country',
        'name' => 'Name',
        'code' => 'Code',
        'status' => 'Status',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCountryFilter(): void
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
        $record = StateModel::findOrFail($id);

        $this->editId = $id;
        $this->country_id = (string) $record->country_id;
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

        StateModel::create([
            'country_id' => (int) $this->country_id,
            'name' => $this->name,
            'code' => $this->code ?: null,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'State created successfully.');
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

        $record = StateModel::findOrFail((int) $this->editId);
        $record->update([
            'country_id' => (int) $this->country_id,
            'name' => $this->name,
            'code' => $this->code ?: null,
            'status' => $this->status ? 1 : 0,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'State updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        $record = StateModel::with('country')->find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $countryName = $record->country?->name;
        $hasLocations = $countryName
            ? DB::table('locations')
                ->where('state', $record->name)
                ->where('country', $countryName)
                ->exists()
            : false;

        if ($hasLocations) {
            $this->dispatch(
                'deleteResult',
                success: false,
                message: 'This state cannot be deleted because it is used in locations.'
            );
            return;
        }

        $record->delete();
        $this->closeModals();
        session()->flash('message', 'State deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'State deleted successfully.');
    }

    public function getItemsProperty()
    {
        $query = StateModel::query()
            ->with('country')
            ->when($this->countryFilter !== '', function ($q) {
                $q->where('country_id', (int) $this->countryFilter);
            })
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhereHas('country', fn ($countryQ) => $countryQ->where('name', 'like', '%' . $search . '%'));
                });
            });

        $allowedSorts = ['id', 'country_id', 'name', 'code', 'status'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'id';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortField, $sortDirection)
            ->get();
    }

    public function getViewRecordProperty(): ?StateModel
    {
        if (! $this->showViewModal || $this->viewId === null) {
            return null;
        }

        return StateModel::with('country')->find($this->viewId);
    }

    public function getCountryOptionsProperty()
    {
        return CountryModel::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.state', [
            'items' => $this->items,
        ]);
    }

    private function resetForm(): void
    {
        $this->country_id = '';
        $this->name = '';
        $this->code = '';
        $this->status = true;
        $this->resetValidation();
    }
}
