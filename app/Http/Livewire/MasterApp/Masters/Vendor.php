<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Vendor as VendorModel;
use App\Models\VendorCategory;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Organization as OrganizationModel;
use Illuminate\Validation\Rule;

class Vendor extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public ?string $organizationFilter = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    // Form fields
    public string $name = '';
    public string $organization_id = '';
    public string $mobile = '';
    public string $email = '';
    public string $companyname = '';
    public string $category_id = '';
    public string $address = '';
    public string $state = '';
    public string $city = '';
    public string $pin = '';
    public string $PAN = '';
    public string $gst = '';
    public bool $status = true;
    public array $banks = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'organizationFilter' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable', 
                'email', 
                Rule::unique('vendors', 'email')->ignore($this->editId)->whereNull('deleted_at')
            ],
            'companyname' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:vendor_categories,id'],
            'address' => ['nullable', 'string'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'pin' => ['nullable', 'string', 'max:20'],
            'PAN' => ['nullable', 'string', 'max:20'],
            'gst' => ['nullable', 'string', 'max:255'],
            'status' => ['boolean'],
            'banks.*.bank_name' => ['required', 'string', 'max:255'],
            'banks.*.ac_number' => ['required', 'string', 'max:255'],
            'banks.*.ifsc_number' => ['required', 'string', 'max:255'],
            'banks.*.ac_type' => ['required', 'string', 'max:255'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingOrganizationFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function addBank(): void
    {
        $this->banks[] = ['bank_name' => '', 'ac_number' => '', 'ifsc_number' => '', 'ac_type' => ''];
    }

    public function removeBank(int $index): void
    {
        unset($this->banks[$index]);
        $this->banks = array_values($this->banks);
    }

    public function openEditModal(int $id): void
    {
        $record = VendorModel::findOrFail($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->organization_id = (string) ($record->organization_id ?? '');
        $this->mobile = $record->mobile ?? '';
        $this->email = $record->email;
        $this->companyname = $record->companyname ?? '';
        $this->category_id = (string) ($record->category_id ?? '');
        $this->address = $record->address ?? '';
        $this->state = $record->state ?? '';
        $this->city = $record->city ?? '';
        $this->pin = $record->pin ?? '';
        $this->PAN = $record->PAN ?? '';
        $this->gst = $record->gst ?? '';
        $this->status = (bool) $record->status;
        $this->showEditModal = true;

        $this->banks = $record->banks->toArray();
        if (empty($this->banks)) {
            $this->addBank();
        }
    }

    public function openViewModal(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
    }

    public function toggleStatus(int $id): void
    {
        $record = VendorModel::findOrFail($id);
        $record->status = !$record->status;
        $record->save();
        $this->dispatch('statusUpdated', active: $record->status, message: 'Status updated.');
    }

    public function saveCreate(): void
    {
        $this->validate();
        $data = $this->all();
        $data['category_id'] = $this->category_id ?: null;
        $data['organization_id'] = $this->organization_id ?: null;
        $vendor = VendorModel::create($data);

        $vendor->banks()->createMany($this->banks);

        $this->dispatch('formResult', type: 'success', message: 'Vendor created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        $this->validate();
        $record = VendorModel::findOrFail($this->editId);
        $data = $this->all();
        $data['category_id'] = $this->category_id ?: null;
        $data['organization_id'] = $this->organization_id ?: null;
        $record->update($data);

        $record->banks()->delete();
        $record->banks()->createMany($this->banks);

        $this->dispatch('formResult', type: 'success', message: 'Vendor updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        VendorModel::destroy($id);
        $this->dispatch('deleteResult', success: true, message: 'Vendor deleted successfully.');
    }

    public function closeModals(): void
    {
        $this->showCreateModal = $this->showEditModal = $this->showViewModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = $this->organization_id = $this->mobile = $this->email = $this->companyname = '';
        $this->category_id = $this->address = $this->state = $this->city = '';
        $this->pin = $this->PAN = $this->gst = '';
        $this->status = true;
        $this->banks = [['bank_name' => '', 'ac_number' => '', 'ifsc_number' => '', 'ac_type' => '']];
        $this->editId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $items = VendorModel::query()
            ->with(['category', 'organization', 'banks'])
            ->when($this->organizationFilter, function($q) {
                $q->where('organization_id', $this->organizationFilter);
            })
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('companyname', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('masterapp.livewire.masters.vendor', [
            'items' => $items,
            'categoryOptions' => VendorCategory::orderBy('name')->get(['id', 'name']),
            'organizationOptions' => OrganizationModel::orderBy('name')->get(['id', 'name']),
            'viewRecord' => $this->viewId ? VendorModel::with(['category', 'organization'])->find($this->viewId) : null
        ]);
    }
}