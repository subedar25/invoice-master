<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Vendor as VendorModel;
use App\Models\VendorCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
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
    ];

    public function boot(): void
    {
        Gate::authorize('vendors');
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('vendors', 'email')->ignore($this->editId)->whereNull('deleted_at'),
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
        ];

        foreach ($this->banks as $index => $bank) {
            $bank = is_array($bank) ? $bank : [];
            if ($this->bankRowHasAnyValue($bank)) {
                $rules["banks.{$index}.bank_name"] = ['required', 'string', 'max:255'];
                $rules["banks.{$index}.ac_number"] = ['required', 'string', 'max:50'];
                $rules["banks.{$index}.ifsc_number"] = ['required', 'string', 'min:5', 'max:25'];
                $rules["banks.{$index}.ac_type"] = ['required', Rule::in(['Savings', 'Current'])];
            } else {
                $rules["banks.{$index}.bank_name"] = ['nullable', 'string', 'max:255'];
                $rules["banks.{$index}.ac_number"] = ['nullable', 'string', 'max:50'];
                $rules["banks.{$index}.ifsc_number"] = ['nullable', 'string', 'max:20'];
                $rules["banks.{$index}.ac_type"] = ['nullable', 'string', 'max:50'];
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $bank
     */
    private function bankRowHasAnyValue(array $bank): bool
    {
        foreach (['bank_name', 'ac_number', 'ifsc_number', 'ac_type'] as $key) {
            $v = $bank[$key] ?? null;
            if ($v !== null && trim((string) $v) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function normalizedBankRowsForSave(): array
    {
        $out = [];
        foreach ($this->banks as $bank) {
            $bank = is_array($bank) ? $bank : [];
            if (! $this->bankRowHasAnyValue($bank)) {
                continue;
            }
            $out[] = [
                'bank_name' => trim((string) ($bank['bank_name'] ?? '')),
                'ac_number' => trim((string) ($bank['ac_number'] ?? '')),
                'ifsc_number' => strtoupper(trim((string) ($bank['ifsc_number'] ?? ''))),
                'ac_type' => (string) ($bank['ac_type'] ?? ''),
            ];
        }

        return $out;
    }

    public function updatingSearch(): void
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
        $record = VendorModel::withTrashed()->findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->editId = $id;
        $this->name = $record->name;
        $this->organization_id = (string) ($selectedOrganizationId ?: $record->organization_id);
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

        $this->banks = $record->banks->map(function ($b) {
            return [
                'bank_name' => $b->bank_name ?? '',
                'ac_number' => $b->ac_number ?? '',
                'ifsc_number' => $b->ifsc_number ?? '',
                'ac_type' => $b->ac_type ?? '',
            ];
        })->values()->all();
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
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        $this->validate();
        $data = $this->all();
        $data['category_id'] = $this->category_id ?: null;
        $data['organization_id'] = $this->organization_id ?: null;
        unset($data['banks']);
        $vendor = VendorModel::create($data);

        $bankRows = $this->normalizedBankRowsForSave();
        if ($bankRows !== []) {
            $vendor->banks()->createMany($bankRows);
        }

        $this->dispatch('formResult', type: 'success', message: 'Vendor created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        $this->validate();
        $record = VendorModel::withTrashed()->findOrFail($this->editId);
        $data = $this->all();
        $data['category_id'] = $this->category_id ?: null;
        $data['organization_id'] = $this->organization_id ?: null;
        unset($data['banks']);
        $record->update($data);

        $record->banks()->delete();
        $bankRows = $this->normalizedBankRowsForSave();
        if ($bankRows !== []) {
            $record->banks()->createMany($bankRows);
        }

        $this->dispatch('formResult', type: 'success', message: 'Vendor updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        $record = VendorModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $record->delete();
        $this->dispatch('deleteResult', success: true, message: 'Vendor deleted successfully.');
    }

    public function restoreById(int $id): void
    {
        if (! $this->isSystemUser()) {
            $this->dispatch('deleteResult', success: false, message: 'Only system user can revert deleted records.');
            return;
        }

        $record = VendorModel::withTrashed()->find($id);
        if (! $record || ! $record->trashed()) {
            $this->dispatch('deleteResult', success: false, message: 'Deleted record not found.');
            return;
        }

        $record->restore();
        $this->dispatch('deleteResult', success: true, message: 'Vendor reverted successfully.');
    }

    public function closeModals(): void
    {
        $this->showCreateModal = $this->showEditModal = $this->showViewModal = false;
        $this->resetForm();
    }

    public function backFromForm(): void
    {
        $this->closeModals();
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->name = $this->mobile = $this->email = $this->companyname = '';
        $this->organization_id = $selectedOrganizationId !== null ? (string) $selectedOrganizationId : '';
        $this->category_id = $this->address = $this->state = $this->city = '';
        $this->pin = $this->PAN = $this->gst = '';
        $this->status = true;
        $this->banks = [];
        $this->editId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $items = VendorModel::query()
            ->with(['category', 'organization', 'banks'])
            ->when($this->isSystemUser(), function ($q) {
                $q->withTrashed();
            })
            ->when($this->organizationFilter, function($q) {
                $q->where('organization_id', $this->organizationFilter);
            })
            ->when($this->search, function($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('companyname', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('masterapp.livewire.masters.vendor', [
            'items' => $items,
            'categoryOptions' => VendorCategory::orderBy('name')->get(['id', 'name']),
            'viewRecord' => $this->viewId ? VendorModel::withTrashed()->with(['category', 'organization', 'banks'])->find($this->viewId) : null
        ]);
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

    private function isSystemUser(): bool
    {
        return (auth()->user()?->user_type ?? '') === 'systemuser';
    }
}