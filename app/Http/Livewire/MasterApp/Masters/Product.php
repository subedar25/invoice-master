<?php

namespace App\Http\Livewire\MasterApp\Masters;

use App\Models\Organization as OrganizationModel;
use App\Models\Product as ProductModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Product extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public ?string $organizationFilter = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    public string $organization_id = '';
    public string $name = '';
    public string $unit_price = '0.00';
    public string $hsn = '';
    public string $cgst = '0.00';
    public string $sgst = '0.00';
    public string $total_gst = '0.00';
    public string $final_price = '0.00';
    public bool $status = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function boot(): void
    {
        Gate::authorize('products');
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
        $uniqueRule = Rule::unique('products', 'name')->where(function ($query) {
            return $query
                ->where('organization_id', (int) $this->organization_id)
                ->whereNull('deleted_at');
        });

        if ($this->editId) {
            $uniqueRule->ignore($this->editId);
        }

        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'hsn' => ['nullable', 'string', 'max:255'],
            'cgst' => ['nullable', 'numeric', 'min:0'],
            'sgst' => ['nullable', 'numeric', 'min:0'],
            'total_gst' => ['nullable', 'numeric', 'min:0'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'organization_id' => 'Organization',
        'name' => 'Product Name',
        'unit_price' => 'Unit Price',
        'hsn' => 'HSN',
        'cgst' => 'CGST',
        'sgst' => 'SGST',
        'total_gst' => 'Total GST',
        'final_price' => 'Final Price',
        'status' => 'Status',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingOrganizationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedUnitPrice(): void
    {
        $this->recalculateTaxValues();
    }

    public function updatedCgst(): void
    {
        $this->recalculateTaxValues();
    }

    public function updatedSgst(): void
    {
        $this->recalculateTaxValues();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        if ($this->organizationFilter !== '') {
            $this->organization_id = $this->organizationFilter;
        }
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

        $record = ProductModel::withTrashed()->findOrFail($id);
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();

        $this->editId = $id;
        $this->organization_id = (string) ($selectedOrganizationId ?: $record->organization_id);
        $this->name = $record->name;
        $this->unit_price = number_format((float) $record->unit_price, 2, '.', '');
        $this->hsn = $record->hsn ?? '';
        $this->cgst = number_format((float) $record->cgst, 2, '.', '');
        $this->sgst = number_format((float) $record->sgst, 2, '.', '');
        $this->total_gst = number_format((float) $record->total_gst, 2, '.', '');
        $this->final_price = number_format((float) $record->final_price, 2, '.', '');
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

        $this->recalculateTaxValues();

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }

        ProductModel::create($this->payload());

        $this->dispatch('formResult', type: 'success', message: 'Product created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        if ($selectedOrganizationId !== null) {
            $this->organization_id = (string) $selectedOrganizationId;
        }

        $this->recalculateTaxValues();

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();
            $this->dispatch('formResult', type: 'error', message: $message ?: 'Validation failed.');
            throw $e;
        }

        $record = ProductModel::withTrashed()->findOrFail((int) $this->editId);
        $record->update($this->payload());

        $this->dispatch('formResult', type: 'success', message: 'Product updated successfully.');
        $this->closeModals();
    }

    public function toggleStatus(int $id): void
    {
        $record = ProductModel::findOrFail($id);
        $record->status = ! $record->status;
        $record->save();

        $this->dispatch('statusUpdated', active: $record->status, message: 'Product status updated.');
    }

    public function deleteById(int $id): void
    {
        if (! $this->canDeleteRecord()) {
            $this->dispatch('deleteResult', success: false, message: 'You are not authorized to delete this record.');
            return;
        }

        $record = ProductModel::find($id);
        if (! $record) {
            $this->dispatch('deleteResult', success: false, message: 'Record not found.');
            return;
        }

        $isUsedInInvoices = DB::table('invoice_details')->where('product_id', $id)->exists();
        if ($isUsedInInvoices) {
            $this->dispatch(
                'deleteResult',
                success: false,
                message: 'This product cannot be deleted because it is already used in invoices.'
            );
            return;
        }

        $record->delete();
        $this->closeModals();
        session()->flash('message', 'Product deleted successfully.');
        $this->dispatch('deleteResult', success: true, message: 'Product deleted successfully.');
    }

    public function restoreById(int $id): void
    {
        if (! $this->isSystemUser()) {
            $this->dispatch('deleteResult', success: false, message: 'Only system user can revert deleted records.');
            return;
        }

        $record = ProductModel::withTrashed()->find($id);
        if (! $record || ! $record->trashed()) {
            $this->dispatch('deleteResult', success: false, message: 'Deleted record not found.');
            return;
        }

        $record->restore();
        $this->dispatch('deleteResult', success: true, message: 'Product reverted successfully.');
    }

    public function getItemsProperty()
    {
        $allowedSorts = ['name', 'unit_price', 'final_price', 'created_at'];
        $sortField = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return ProductModel::query()
            ->with('organization')
            ->when($this->isSystemUser(), function ($q) {
                $q->withTrashed();
            })
            ->when($this->organizationFilter !== '', function ($q) {
                $q->where('organization_id', (int) $this->organizationFilter);
            })
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('hsn', 'like', '%' . $search . '%')
                        ->orWhereHas('organization', fn ($org) => $org->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(15);
    }

    public function getOrganizationOptionsProperty()
    {
        return OrganizationModel::orderBy('name')->get(['id', 'name']);
    }

    public function getViewRecordProperty(): ?ProductModel
    {
        if (! $this->viewId) {
            return null;
        }

        return ProductModel::withTrashed()->with('organization')->find($this->viewId);
    }

    public function render()
    {
        return view('masterapp.livewire.masters.product', [
            'items' => $this->items,
        ]);
    }

    private function recalculateTaxValues(): void
    {
        $unitPrice = (float) ($this->unit_price ?: 0);
        $cgst = (float) ($this->cgst ?: 0);
        $sgst = (float) ($this->sgst ?: 0);
        $totalGst = $cgst + $sgst;
        $finalPrice = $unitPrice + ($unitPrice * $totalGst / 100);

        $this->total_gst = number_format($totalGst, 2, '.', '');
        $this->final_price = number_format($finalPrice, 2, '.', '');
    }

    private function payload(): array
    {
        return [
            'organization_id' => (int) $this->organization_id,
            'name' => $this->name,
            'unit_price' => $this->unit_price,
            'hsn' => $this->hsn ?: null,
            'cgst' => $this->cgst ?: 0,
            'sgst' => $this->sgst ?: 0,
            'total_gst' => $this->total_gst ?: 0,
            'final_price' => $this->final_price ?: 0,
            'status' => $this->status ? 1 : 0,
        ];
    }

    private function resetForm(): void
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId();
        $this->organization_id = $selectedOrganizationId !== null ? (string) $selectedOrganizationId : '';
        $this->name = '';
        $this->unit_price = '0.00';
        $this->hsn = '';
        $this->cgst = '0.00';
        $this->sgst = '0.00';
        $this->total_gst = '0.00';
        $this->final_price = '0.00';
        $this->status = true;
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

    private function isSystemUser(): bool
    {
        return (auth()->user()?->user_type ?? '') === 'systemuser';
    }

    private function canEditRecord(): bool
    {
        return (bool) (auth()->user()?->can('edit-product') ?? false);
    }

    private function canDeleteRecord(): bool
    {
        return (bool) (auth()->user()?->can('delete-product') ?? false);
    }
}
