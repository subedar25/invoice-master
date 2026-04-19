<?php

namespace App\Http\Livewire\Invoice;

use App\Core\Invoice\Services\InvoiceService;
use App\Models\Invoice;
use App\Models\InvoiceStatusHistory;
use App\Models\Ledger;
use App\Models\LedgerStatusHistory;
use App\Support\InvoiceDepartmentAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Invoices extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    /** @var int Allowed: 10, 15, 25, 50, 100 */
    public int $perPage = 15;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;
    public bool $showAddOutletModal = false;
    public bool $showAddVendorModal = false;
    public bool $showAddProductModal = false;

    public bool $showPaymentModal = false;
    public bool $showApproveModal = false;

    public ?int $paymentInvoiceId = null;

    public string $payment_pay_to = '';

    public float $payment_pending_amount = 0;

    public string $payment_amount = '';

    public string $payment_method = 'online';

    public string $payment_description = '';

    public string $payment_status = 'pending';
    public ?int $approveInvoiceId = null;
    public string $approve_comment = '';

    public bool $showPaymentHistoryModal = false;

    public ?int $paymentHistoryInvoiceId = null;

    public string $paymentHistoryInvoiceNumber = '';

    /** @var array<int, array<string, mixed>> */
    public array $paymentHistoryLedgers = [];

    /** @var array<int, array<string, mixed>> */
    public array $paymentHistoryActivity = [];

    public ?int $editId = null;
    public ?int $viewId = null;

    /** @var array<int, string> Lowercase: approve, pending, in_process, complete */
    public array $filterStatuses = [];

    /** @var array<int, int|string> */
    public array $filterDepartmentIds = [];

    /** @var string current_month|last_month|last_3_months|last_6_months|last_12_months|custom — default last 3 months */
    public string $invoiceDateFilterPreset = 'last_3_months';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public bool $invoiceFiltersOpen = false;

    // Form fields
    public $invoice_number, $organization_id, $vendor_id, $department_id, $outlet_id, $pay_term, $comp_date, $year, $description, $total_amount, $paid_amount;
    public $status = 'Pending';
    public $order_status = 'pending';
    public $task_status = 'pending';
    
    public $gross_total = 0;
    public $tax_total = 0;
    
    public $priority = 'Medium';

    public array $invoice_items = [];
    
    public $uploaded_files = [];
    public array $existing_files = [];
    
    public string $new_outlet_name = '';
    public string $new_outlet_location_id = '';
    
    public string $new_vendor_name = '';
    public string $new_vendor_mobile = '';
    public string $new_vendor_email = '';
    
    public string $new_product_name = '';
    public string $new_product_price = '0';
    public string $new_product_hsn = '';
    public string $new_product_cgst = '0';
    public string $new_product_sgst = '0';
    public string $new_product_total_gst = '0';
    public string $new_product_final_price = '0';
    public ?int $pendingProductRowIndex = null;

    private ?InvoiceService $invoiceService = null;

    private function invoice(): InvoiceService
    {
        return $this->invoiceService ??= app(InvoiceService::class);
    }

    protected function rules(): array
    {
        return [
            'invoice_number' => ['nullable', 'string'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'outlet_id' => ['required', 'exists:outlets,id'],
            'total_amount' => ['required', 'numeric'],
            'priority' => ['required', 'string', 'in:High,Medium,Low'],
            'status' => ['required', 'string', 'in:Approve,Pending,in_process,Complete'],
            'invoice_items' => ['required', 'array', 'min:1'],
            'invoice_items.*.product_desciption' => ['required', 'string'],
            'invoice_items.*.quantity' => ['required', 'numeric', 'min:1'],
            'invoice_items.*.unit_price' => ['required', 'numeric'],
            'pay_term' => ['nullable', 'string', 'max:65535'],
            'comp_date' => ['nullable', 'date'],
            'uploaded_files' => ['nullable', 'array'],
            'uploaded_files.*' => ['file', 'max:10240'],
        ];
    }

    public function mount()
    {
        $this->organization_id = $this->resolveDefaultOrganizationId();
        $this->assertMayViewInvoiceIndex();
        $this->resetLineItems();
        $this->calculateGrandTotal();
    }

    /**
     * Keep the list view in sync when the user switches organization in the session (without a full reload).
     */
    public function hydrate(): void
    {
        if ($this->showCreateModal || $this->showEditModal || $this->showViewModal) {
            return;
        }
        $resolved = $this->resolveDefaultOrganizationId();
        if ($resolved !== $this->organization_id) {
            $this->organization_id = $resolved;
        }
        $this->assertMayViewInvoiceIndex();
    }

    /**
     * When an organization is selected, forbid the page if the user cannot list invoices there
     * (no permission, or department scope allows no rows).
     */
    private function assertMayViewInvoiceIndex(): void
    {
        $orgId = $this->organization_id;
        if ($orgId === null) {
            return;
        }

        $user = auth()->user();
        abort_unless(
            InvoiceDepartmentAuthorization::userHasListInOrganization($user, $orgId),
            403,
            'You do not have permission to view invoices for this organization.'
        );

        $restriction = InvoiceDepartmentAuthorization::mergedListDepartmentRestriction($user, $orgId);
        if (
            $restriction === []
            && ! InvoiceDepartmentAuthorization::listOwnInvoicesOnly($user, $orgId)
        ) {
            abort(403, 'You do not have access to invoices for this organization.');
        }
    }

    /**
     * Whether the current user may set status to Approve for the department selected in the form.
     */
    public function canApproveInForm(): bool
    {
        if ($this->editId) {
            $existing = Invoice::query()->select(['id', 'createdby_id', 'organization_id', 'department_id'])->find((int) $this->editId);
            if ($existing && (int) $existing->createdby_id === (int) auth()->id()) {
                return false;
            }

            if ($existing) {
                return InvoiceDepartmentAuthorization::canApproveInvoice(
                    auth()->user(),
                    (int) $existing->organization_id,
                    $existing->department_id !== null ? (int) $existing->department_id : null,
                    $existing->createdby_id !== null ? (int) $existing->createdby_id : null
                );
            }
        }

        $deptId = $this->department_id !== null && $this->department_id !== ''
            ? (int) $this->department_id
            : null;

        return InvoiceDepartmentAuthorization::canApproveInvoice(
            auth()->user(),
            $this->organization_id ?? $this->resolveDefaultOrganizationId(),
            $deptId,
            null
        );
    }

    /**
     * Pager only (summary text is in the card footer). Avoids Livewire’s stock bootstrap
     * view hiding numbered links behind d-none / responsive flex utilities with BS4.
     */
    public function paginationView(): string
    {
        return 'livewire.invoice-pagination';
    }

    public function updatedFilterStatuses(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDepartmentIds(): void
    {
        $this->resetPage();
    }

    /**
     * Called from invoice filter Select2 (change) — wire:model does not sync reliably with Select2.
     *
     * @param  array<int, string>|string|null  $raw
     */
    public function syncFilterStatusesFromSelect(mixed $raw): void
    {
        $allowed = ['approve', 'pending', 'in_process', 'complete'];
        if (! is_array($raw)) {
            $raw = ($raw === null || $raw === '') ? [] : [$raw];
        }
        $this->filterStatuses = array_values(array_intersect($allowed, array_map('strval', $raw)));
        $this->resetPage();
    }

    /**
     * @param  array<int, int|string>|int|string|null  $raw
     */
    public function syncFilterDepartmentIdsFromSelect(mixed $raw): void
    {
        if (! is_array($raw)) {
            $raw = ($raw === null || $raw === '') ? [] : [$raw];
        }
        $this->filterDepartmentIds = array_values(array_unique(array_filter(array_map('intval', $raw))));
        $this->resetPage();
    }

    public function removeInvoiceFilterStatus(string $key): void
    {
        $this->filterStatuses = array_values(array_filter(
            $this->filterStatuses,
            fn ($s) => (string) $s !== $key
        ));
        $this->resetPage();
    }

    public function removeInvoiceFilterDepartment(int $id): void
    {
        $this->filterDepartmentIds = array_values(array_filter(
            array_map('intval', $this->filterDepartmentIds),
            fn ($d) => $d !== $id
        ));
        $this->resetPage();
    }

    public function clearInvoiceSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatingInvoiceDateFilterPreset(mixed $value): void
    {
        if ((string) $value === 'custom' && $this->invoiceDateFilterPreset !== 'custom') {
            [$s, $e] = $this->computeBoundsForPreset($this->invoiceDateFilterPreset);
            $this->dateFrom = $s->toDateString();
            $this->dateTo = $e->toDateString();
        }
    }

    public function updatedInvoiceDateFilterPreset(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        if ($this->invoiceDateFilterPreset === 'custom') {
            $this->resetPage();
        }
    }

    public function updatedDateTo(): void
    {
        if ($this->invoiceDateFilterPreset === 'custom') {
            $this->resetPage();
        }
    }

    public function resetInvoiceDateFilterToDefault(): void
    {
        $this->invoiceDateFilterPreset = 'last_3_months';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function computeBoundsForPreset(string $preset): array
    {
        $now = now();

        return match ($preset) {
            'current_month' => [
                $now->copy()->startOfMonth()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth()->startOfDay(),
                $now->copy()->subMonth()->endOfMonth()->endOfDay(),
            ],
            'last_3_months' => [
                $now->copy()->subMonths(3)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'last_6_months' => [
                $now->copy()->subMonths(6)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'last_12_months' => [
                $now->copy()->subYear()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            default => [
                $now->copy()->subMonths(3)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Filter list by `invoices.created_at` (inclusive).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveCreatedAtRangeForQuery(): array
    {
        if ($this->invoiceDateFilterPreset === 'custom') {
            $now = now();
            $tz = config('app.timezone');
            $from = ($this->dateFrom !== null && $this->dateFrom !== '')
                ? Carbon::parse($this->dateFrom, $tz)->startOfDay()
                : $now->copy()->subMonths(3)->startOfDay();
            $to = ($this->dateTo !== null && $this->dateTo !== '')
                ? Carbon::parse($this->dateTo, $tz)->endOfDay()
                : $now->copy()->endOfDay();
            if ($from->gt($to)) {
                return [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to];
        }

        return $this->computeBoundsForPreset($this->invoiceDateFilterPreset);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $allowed = [10, 15, 25, 50, 100];
        $p = (int) $this->perPage;
        if (! in_array($p, $allowed, true)) {
            $p = 15;
        }
        $this->perPage = $p;
        $this->resetPage();
    }

    public function resetLineItems()
    {
        $this->invoice_items = [
            ['product_id' => null, 'product_desciption' => '', 'quantity' => 1, 'unit_price' => 0, 'hsn' => '', 'cgst' => 0, 'sgst' => 0, 'total_gst' => 0, 'total_price' => 0, 'total_amount' => 0, 'discount' => 0]
        ];
    }

    public function addLineItem()
    {
        $this->invoice_items[] = ['product_id' => null, 'product_desciption' => '', 'quantity' => 1, 'unit_price' => 0, 'hsn' => '', 'cgst' => 0, 'sgst' => 0, 'total_gst' => 0, 'total_price' => 0, 'total_amount' => 0, 'discount' => 0];
    }

    public function removeLineItem($index)
    {
        unset($this->invoice_items[$index]);
        $this->invoice_items = array_values($this->invoice_items);
    }

    public function updatedOrganizationId($value)
    {
        $this->vendor_id = null;
        $this->department_id = null;
        $this->outlet_id = null;

        foreach ($this->invoice_items as $index => $item) {
            $this->invoice_items[$index]['product_id'] = null;
            $this->invoice_items[$index]['product_desciption'] = '';
            $this->invoice_items[$index]['unit_price'] = 0;
            $this->invoice_items[$index]['hsn'] = '';
            $this->invoice_items[$index]['cgst'] = 0;
            $this->invoice_items[$index]['sgst'] = 0;
            $this->invoice_items[$index]['total_gst'] = 0;
            $this->invoice_items[$index]['total_price'] = 0;
        }

        $this->calculateGrandTotal();
    }

    public function updatedInvoiceItems($value, $key)
    {
        $parts = explode('.', $key);

        // Live math recalculators dynamically hook upon manually shifting target bounds seamlessly.
        if (count($parts) == 2 && in_array($parts[1], ['quantity', 'unit_price', 'cgst', 'sgst'])) {
            $this->calculateRowTotal($parts[0]);
        }
    }

    public function selectProduct($index, $productId)
    {
        $product = $this->invoice()->findProductForOrganization((int) $productId, $this->organization_id);

        if ($product) {
            $this->invoice_items[$index]['product_id'] = $product->id;
            $this->invoice_items[$index]['product_desciption'] = $product->name;
            $this->invoice_items[$index]['unit_price'] = $product->unit_price ?? 0;
            $this->invoice_items[$index]['hsn'] = $product->hsn ?? '';
            $this->invoice_items[$index]['cgst'] = $product->cgst ?? 0;
            $this->invoice_items[$index]['sgst'] = $product->sgst ?? 0;
            $this->invoice_items[$index]['show_dropdown'] = false;

            $this->calculateRowTotal($index);
        }
    }

    public function calculateRowTotal($index)
    {
        $qty = (float)($this->invoice_items[$index]['quantity'] ?? 0);
        $price = (float)($this->invoice_items[$index]['unit_price'] ?? 0);
        $cgst = (float)($this->invoice_items[$index]['cgst'] ?? 0);
        $sgst = (float)($this->invoice_items[$index]['sgst'] ?? 0);

        $base = $qty * $price;
        $total = $base + ($base * ($cgst + $sgst) / 100);

        $this->invoice_items[$index]['total_price'] = number_format($total, 2, '.', '');
        
        $this->calculateGrandTotal();
    }

    public function calculateGrandTotal()
    {
        $grossSum = 0;
        $taxSum = 0;
        
        foreach ($this->invoice_items as $item) {
            $qty = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unit_price'] ?? 0);
            $cgst = (float)($item['cgst'] ?? 0);
            $sgst = (float)($item['sgst'] ?? 0);

            $base = $qty * $price;
            $taxAmount = $base * ($cgst + $sgst) / 100;

            $grossSum += $base;
            $taxSum += $taxAmount;
        }

        $this->gross_total = number_format($grossSum, 2, '.', '');
        $this->tax_total = number_format($taxSum, 2, '.', '');
        $this->total_amount = number_format($grossSum + $taxSum, 2, '.', '');
    }

    public function openCreateModal()
    {
        if ($this->resolveDefaultOrganizationId() === null) {
            $this->dispatch('formResult', type: 'warning', message: 'Select an organization before creating an invoice.');

            return;
        }
        $this->resetForm();
        $this->organization_id = $this->resolveDefaultOrganizationId();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $this->editId = $id;
        $record = $this->invoice()->findForEdit($id);
        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $record->organization_id,
                $record->department_id !== null ? (int) $record->department_id : null,
                $record->createdby_id !== null ? (int) $record->createdby_id : null
            ),
            403
        );
        
        $this->organization_id = $record->organization_id;
        $this->invoice_number = $record->invoice_number;
        $this->organization_id = $record->organization_id;
        $this->outlet_id = $record->outlet_id;
        $this->vendor_id = $record->vendor_id;
        $this->department_id = $record->department_id;
        $this->pay_term = $record->pay_term;
        $this->comp_date = $record->comp_date
            ? $record->comp_date->format('Y-m-d')
            : null;
        $this->year = $record->year;
        $this->description = $record->description;
        $this->total_amount = number_format((float)$record->total_amount, 2, '.', '');
        $this->paid_amount = $record->paid_amount;
        $this->status = $this->normalizeStatus($record->status);
        $this->priority = $record->priority ?? 'Medium';
        $this->invoice_items = $record->details->toArray();
        $this->existing_files = $record->files->toArray();
        $this->showEditModal = true;
        
        // Auto-run totals to populate grid safely on edit open
        $this->calculateGrandTotal();
    }

    public function openViewModal(int $id)
    {
        $record = $this->invoice()->findForView($id);
        abort_unless($record, 404);
        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $record->organization_id,
                $record->department_id !== null ? (int) $record->department_id : null,
                $record->createdby_id !== null ? (int) $record->createdby_id : null
            ),
            403
        );
        $this->viewId = $id;
        $this->showViewModal = true;
    }

    public function openApproveModal(int $id): void
    {
        abort_unless(auth()->user()->can('approve-invoice'), 403);

        $invoice = Invoice::query()->findOrFail($id);
        abort_if(
            (int) $invoice->createdby_id === (int) auth()->id(),
            403,
            'You cannot approve your own invoice.'
        );
        abort_unless(
            InvoiceDepartmentAuthorization::canApproveInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        if ($this->normalizeStatus((string) $invoice->status) === 'Approve') {
            $this->dispatch('formResult', type: 'warning', message: 'This invoice is already approved.');

            return;
        }

        $this->resetValidation();
        $this->approveInvoiceId = $invoice->id;
        $this->approve_comment = '';
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->approveInvoiceId = null;
        $this->approve_comment = '';
        $this->resetValidation();
    }

    public function approveInvoice(): void
    {
        abort_unless(auth()->user()->can('approve-invoice'), 403);
        if ($this->approveInvoiceId === null) {
            return;
        }

        $this->validate([
            'approve_comment' => ['required', 'string', 'min:2', 'max:2000'],
        ], [], [
            'approve_comment' => 'comment',
        ]);

        $invoice = Invoice::query()->findOrFail($this->approveInvoiceId);
        abort_if(
            (int) $invoice->createdby_id === (int) auth()->id(),
            403,
            'You cannot approve your own invoice.'
        );
        abort_unless(
            InvoiceDepartmentAuthorization::canApproveInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        $fromStatus = $this->normalizeStatus((string) $invoice->status);
        if ($fromStatus === 'Approve') {
            $this->dispatch('formResult', type: 'warning', message: 'This invoice is already approved.');
            $this->closeApproveModal();

            return;
        }

        DB::transaction(function () use ($invoice, $fromStatus) {
            $invoice->status = 'Approve';
            $invoice->save();

            InvoiceStatusHistory::query()->create([
                'invoice_id' => (int) $invoice->id,
                'user_id' => (int) auth()->id(),
                'from_status' => $fromStatus,
                'to_status' => 'Approve',
                'comment' => trim($this->approve_comment),
                'created_at' => now(),
            ]);
        });

        $this->dispatch('formResult', type: 'success', message: 'Invoice approved successfully.');
        $this->closeApproveModal();
    }

    public function openPaymentModal(int $id): void
    {
        abort_unless(auth()->user()->can('edit-invoice'), 403);

        $invoice = Invoice::query()->with('vendor')->findOrFail($id);
        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        $pending = max(0, (float) $invoice->total_amount - (float) ($invoice->paid_amount ?? 0));
        if ($pending <= 0) {
            $this->dispatch('formResult', type: 'warning', message: 'This invoice has no pending balance.');

            return;
        }

        $this->resetValidation();
        $this->paymentInvoiceId = $invoice->id;
        $this->payment_pay_to = $invoice->vendor?->name ?? 'N/A';
        $this->payment_pending_amount = round($pending, 2);
        $this->payment_amount = number_format($this->payment_pending_amount, 2, '.', '');
        $this->payment_method = 'online';
        $this->payment_description = '';
        $this->payment_status = 'pending';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentInvoiceId = null;
        $this->payment_pay_to = '';
        $this->payment_pending_amount = 0;
        $this->payment_amount = '';
        $this->payment_method = 'online';
        $this->payment_description = '';
        $this->payment_status = 'pending';
        $this->resetValidation();
    }

    public function savePayment(): void
    {
        abort_unless(auth()->user()->can('edit-invoice'), 403);

        if ($this->paymentInvoiceId === null) {
            return;
        }

        $this->validate([
            'payment_method' => ['required', Rule::in(['cheque', 'cash', 'online'])],
            'payment_description' => ['nullable', 'string', 'max:65535'],
            'payment_amount' => ['required', 'numeric', 'min:0.01', 'max:'.$this->payment_pending_amount],
            'payment_status' => ['required', Rule::in(['pending', 'cancelled', 'completed', 'failed', 'invalid'])],
        ], [], [
            'payment_method' => 'payment method',
            'payment_description' => 'description',
            'payment_amount' => 'amount',
            'payment_status' => 'status',
        ]);

        $amount = round((float) $this->payment_amount, 2);
        $pending = round((float) $this->payment_pending_amount, 2);
        if ($amount > $pending) {
            $this->addError('payment_amount', 'Amount cannot exceed the pending balance.');

            return;
        }

        $invoice = Invoice::query()->with('vendor')->findOrFail($this->paymentInvoiceId);
        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        DB::transaction(function () use ($invoice, $amount) {
            $ledger = Ledger::query()->create([
                'user_id' => (int) auth()->id(),
                'vendor_id' => (int) $invoice->vendor_id,
                'invoice_id' => (int) $invoice->id,
                'total_amount' => $amount,
                'payment_method' => $this->payment_method,
                'payment_type' => 'dr',
                'description' => $this->payment_description !== '' ? $this->payment_description : null,
                'created_date' => now(),
                'status' => $this->payment_status,
            ]);

            if ($this->payment_status === 'completed') {
                $invoice->refresh();
                $invoice->paid_amount = round((float) ($invoice->paid_amount ?? 0) + $amount, 2);
                $invoice->save();
            }

            LedgerStatusHistory::query()->create([
                'ledger_id' => $ledger->id,
                'invoice_id' => $invoice->id,
                'user_id' => (int) auth()->id(),
                'from_status' => null,
                'to_status' => $this->payment_status,
                'detail' => $this->initialLedgerHistoryDetail($this->payment_status, $amount),
                'created_at' => now(),
            ]);
        });

        $this->dispatch('formResult', type: 'success', message: 'Payment recorded successfully.');
        $this->closePaymentModal();
    }

    public function openPaymentHistoryModal(int $id): void
    {
        abort_unless(auth()->user()->can('list-invoices'), 403);

        $invoice = Invoice::query()
            ->with([
                'ledgers' => static function ($q) {
                    $q->orderByDesc('created_date')->orderByDesc('id');
                },
                'ledgers.user',
            ])
            ->findOrFail($id);

        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        $this->fillPaymentHistoryFromInvoice($invoice);
        $this->showPaymentHistoryModal = true;
    }

    public function updateLedgerStatus(int $ledgerId, string $newStatus): void
    {
        abort_unless(auth()->user()->can('edit-invoice'), 403);

        $newStatus = strtolower(trim($newStatus));
        if (! in_array($newStatus, ['pending', 'cancelled', 'completed', 'failed', 'invalid'], true)) {
            $this->dispatch('formResult', type: 'error', message: 'Invalid payment status.');

            return;
        }

        $ledger = Ledger::query()->with('invoice')->findOrFail($ledgerId);
        $invoice = $ledger->invoice;
        abort_unless($invoice, 404);

        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        $old = strtolower((string) $ledger->status);

        if ($old === 'completed') {
            $this->dispatch('formResult', type: 'warning', message: 'Completed payments cannot be changed from the list. Use Revert to record a reversal and update the invoice balance.');

            return;
        }

        if ($old === $newStatus) {
            return;
        }

        $amount = round((float) $ledger->total_amount, 2);

        $countsTowardPaid = static fn (string $s): bool => $s === 'completed';

        try {
            DB::transaction(function () use ($ledger, $invoice, $old, $newStatus, $amount, $countsTowardPaid) {
                $invoice->refresh();

                $wasApplied = $countsTowardPaid($old);
                $willApply = $countsTowardPaid($newStatus);

                if (! $wasApplied && $willApply) {
                    $newPaid = round((float) ($invoice->paid_amount ?? 0) + $amount, 2);
                    $invTotal = (float) $invoice->total_amount;
                    if ($newPaid > $invTotal + 0.0001) {
                        throw new \RuntimeException('EXCEEDS_INVOICE_TOTAL');
                    }
                    $invoice->paid_amount = $newPaid;
                    $invoice->save();
                } elseif ($wasApplied && ! $willApply) {
                    $invoice->paid_amount = max(0, round((float) ($invoice->paid_amount ?? 0) - $amount, 2));
                    $invoice->save();
                }

                $ledger->status = $newStatus;
                $ledger->save();

                LedgerStatusHistory::query()->create([
                    'ledger_id' => $ledger->id,
                    'invoice_id' => $invoice->id,
                    'user_id' => (int) auth()->id(),
                    'from_status' => $old,
                    'to_status' => $newStatus,
                    'detail' => $this->ledgerStatusChangeDetail($old, $newStatus, $wasApplied, $willApply, $amount),
                    'created_at' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'EXCEEDS_INVOICE_TOTAL') {
                $this->dispatch('formResult', type: 'error', message: 'Cannot mark complete: total paid would exceed the invoice amount.');

                return;
            }
            throw $e;
        }

        if ($this->paymentHistoryInvoiceId === (int) $ledger->invoice_id) {
            $refreshed = Invoice::query()
                ->with([
                    'ledgers' => static function ($q) {
                        $q->orderByDesc('created_date')->orderByDesc('id');
                    },
                    'ledgers.user',
                ])
                ->findOrFail($ledger->invoice_id);
            $this->fillPaymentHistoryFromInvoice($refreshed);
        }

        $this->dispatch('formResult', type: 'success', message: 'Payment status updated.');
    }

    /**
     * Revert a completed payment (removes amount from invoice paid total). Recorded in ledger status history — not via the status dropdown.
     */
    public function revertCompletedLedgerPayment(int $ledgerId): void
    {
        abort_unless(auth()->user()->can('edit-invoice'), 403);

        $ledger = Ledger::query()->with('invoice')->findOrFail($ledgerId);
        $invoice = $ledger->invoice;
        abort_unless($invoice, 404);

        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $invoice->organization_id,
                $invoice->department_id !== null ? (int) $invoice->department_id : null,
                $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
            ),
            403
        );

        $old = strtolower((string) $ledger->status);
        if ($old !== 'completed') {
            $this->dispatch('formResult', type: 'warning', message: 'Only a completed payment can be reverted here.');

            return;
        }

        $newStatus = 'cancelled';
        $amount = round((float) $ledger->total_amount, 2);

        DB::transaction(function () use ($ledger, $invoice, $old, $newStatus, $amount) {
            $invoice->refresh();
            $invoice->paid_amount = max(0, round((float) ($invoice->paid_amount ?? 0) - $amount, 2));
            $invoice->save();

            $ledger->status = $newStatus;
            $ledger->save();

            LedgerStatusHistory::query()->create([
                'ledger_id' => $ledger->id,
                'invoice_id' => $invoice->id,
                'user_id' => (int) auth()->id(),
                'from_status' => $old,
                'to_status' => $newStatus,
                'detail' => 'Payment reverted from history: '.number_format($amount, 2).' removed from invoice paid total (Completed → Cancelled).',
                'created_at' => now(),
            ]);
        });

        if ($this->paymentHistoryInvoiceId === (int) $ledger->invoice_id) {
            $refreshed = Invoice::query()
                ->with([
                    'ledgers' => static function ($q) {
                        $q->orderByDesc('created_date')->orderByDesc('id');
                    },
                    'ledgers.user',
                ])
                ->findOrFail($ledger->invoice_id);
            $this->fillPaymentHistoryFromInvoice($refreshed);
        }

        $this->dispatch('formResult', type: 'success', message: 'Payment reverted and activity recorded.');
    }

    private function fillPaymentHistoryFromInvoice(Invoice $invoice): void
    {
        $this->paymentHistoryInvoiceId = $invoice->id;
        $this->paymentHistoryInvoiceNumber = (string) ($invoice->invoice_number ?? '#'.$invoice->id);
        $this->paymentHistoryLedgers = $invoice->ledgers->map(function (Ledger $l) {
            $u = $l->user;

            return [
                'id' => $l->id,
                'created_date' => $l->created_date?->format('Y-m-d H:i') ?? '—',
                'payment_method' => ucfirst((string) $l->payment_method),
                'total_amount' => (float) $l->total_amount,
                'status' => (string) $l->status,
                'description' => $l->description,
                'recorded_by' => $u ? (trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: '—') : '—',
            ];
        })->values()->all();

        $this->paymentHistoryActivity = LedgerStatusHistory::query()
            ->where('invoice_id', $invoice->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->with('user')
            ->get()
            ->map(function (LedgerStatusHistory $h) {
                $u = $h->user;

                return [
                    'at' => $h->created_at?->format('Y-m-d H:i') ?? '—',
                    'ledger_id' => $h->ledger_id,
                    'detail' => $h->detail ?: $this->fallbackHistoryLine($h),
                    'by' => $u ? (trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: '—') : '—',
                ];
            })->values()->all();
    }

    private function initialLedgerHistoryDetail(string $status, float $amount): string
    {
        $status = strtolower($status);
        $amt = number_format($amount, 2);
        if ($status === 'completed') {
            return "Payment recorded as Completed. {$amt} applied to invoice paid total.";
        }

        return "Payment recorded as ".ucfirst($status).". No amount applied to invoice paid total until status is Completed.";
    }

    private function ledgerStatusChangeDetail(
        string $old,
        string $new,
        bool $wasApplied,
        bool $willApply,
        float $amount
    ): string {
        $amt = number_format($amount, 2);
        $line = ucfirst($old).' → '.ucfirst($new).'.';

        if (! $wasApplied && $willApply) {
            return $line." {$amt} applied to invoice paid total.";
        }

        if ($wasApplied && ! $willApply) {
            return $line." {$amt} reverted from invoice paid total.";
        }

        return $line.' Invoice paid total unchanged.';
    }

    private function fallbackHistoryLine(LedgerStatusHistory $h): string
    {
        $from = $h->from_status !== null && $h->from_status !== ''
            ? ucfirst((string) $h->from_status)
            : '—';

        return 'Status '.$from.' → '.ucfirst((string) $h->to_status).' (ledger #'.$h->ledger_id.').';
    }

    public function closePaymentHistoryModal(): void
    {
        $this->showPaymentHistoryModal = false;
        $this->paymentHistoryInvoiceId = null;
        $this->paymentHistoryInvoiceNumber = '';
        $this->paymentHistoryLedgers = [];
        $this->paymentHistoryActivity = [];
    }

    public function openAddOutletModal()
    {
        $this->new_outlet_name = '';
        $this->new_outlet_location_id = '';
        $this->showAddOutletModal = true;
    }

    public function closeAddOutletModal()
    {
        $this->showAddOutletModal = false;
    }

    public function saveNewOutlet()
    {
        $this->validate([
            'new_outlet_name' => 'required|string|max:255',
            'new_outlet_location_id' => 'required|exists:locations,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $outlet = $this->invoice()->createOutlet([
            'name' => $this->new_outlet_name,
            'location_id' => $this->new_outlet_location_id,
            'organization_id' => $this->organization_id,
            'status' => 1,
        ]);

        $this->outlet_id = $outlet->id;
        $this->closeAddOutletModal();
        $this->dispatch('formResult', type: 'success', message: 'Outlet created seamlessly!');
    }

    public function openAddVendorModal()
    {
        $this->new_vendor_name = '';
        $this->new_vendor_mobile = '';
        $this->new_vendor_email = '';
        $this->showAddVendorModal = true;
    }

    public function closeAddVendorModal()
    {
        $this->showAddVendorModal = false;
    }

    public function saveNewVendor()
    {
        $this->validate([
            'new_vendor_name' => 'required|string|max:255',
            'new_vendor_mobile' => 'nullable|string|max:20',
            'new_vendor_email' => 'nullable|email|max:255',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $vendor = $this->invoice()->createVendor([
            'name' => $this->new_vendor_name,
            'mobile' => $this->new_vendor_mobile,
            'email' => $this->new_vendor_email,
            'organization_id' => $this->organization_id,
            'status' => 1,
        ]);

        $this->vendor_id = $vendor->id;
        $this->closeAddVendorModal();
        $this->dispatch('formResult', type: 'success', message: 'Party Name generated dynamically!');
    }

    public function updatedNewProductCgst($value)
    {
        $this->calculateProductGstAndPrice();
    }

    public function updatedNewProductSgst($value)
    {
        $this->calculateProductGstAndPrice();
    }

    public function updatedNewProductPrice($value)
    {
        $this->calculateProductGstAndPrice();
    }

    public function updatedNewProductTotalGst($value)
    {
        $this->calculateProductFinalPrice();
    }

    private function calculateProductGstAndPrice()
    {
        $cgst = (float)($this->new_product_cgst ?: 0);
        $sgst = (float)($this->new_product_sgst ?: 0);
        $this->new_product_total_gst = (string)($cgst + $sgst);

        $this->calculateProductFinalPrice();
    }

    private function calculateProductFinalPrice()
    {
        $price = (float)($this->new_product_price ?: 0);
        $totalGst = (float)($this->new_product_total_gst ?: 0);

        $this->new_product_final_price = (string)number_format($price + ($price * $totalGst / 100), 2, '.', '');
    }

    public function openAddProductModal($index)
    {
        $this->pendingProductRowIndex = $index;
        $this->new_product_name = '';
        $this->new_product_price = '0';
        $this->new_product_hsn = '';
        $this->new_product_cgst = '0';
        $this->new_product_sgst = '0';
        $this->new_product_total_gst = '0';
        $this->new_product_final_price = '0';
        $this->showAddProductModal = true;
    }

    public function closeAddProductModal()
    {
        $this->showAddProductModal = false;
        $this->pendingProductRowIndex = null;
    }

    public function saveNewProduct()
    {
        $this->validate([
            'new_product_name' => 'required|string|max:255',
            'new_product_price' => 'required|numeric|min:0',
            'new_product_hsn' => 'nullable|string|max:255',
            'new_product_cgst' => 'nullable|numeric|min:0',
            'new_product_sgst' => 'nullable|numeric|min:0',
            'new_product_total_gst' => 'nullable|numeric|min:0',
            'new_product_final_price' => 'nullable|numeric|min:0',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $product = $this->invoice()->createProduct([
            'name' => $this->new_product_name,
            'unit_price' => $this->new_product_price,
            'hsn' => $this->new_product_hsn,
            'cgst' => $this->new_product_cgst ?: 0,
            'sgst' => $this->new_product_sgst ?: 0,
            'total_gst' => $this->new_product_total_gst ?: 0,
            'final_price' => $this->new_product_final_price ?: 0,
            'organization_id' => $this->organization_id,
            'status' => 1,
        ]);

        // Inject the resulting id securely straight back directly into the designated Array element loop row!
        if ($this->pendingProductRowIndex !== null && isset($this->invoice_items[$this->pendingProductRowIndex])) {
            $this->invoice_items[$this->pendingProductRowIndex]['product_id'] = $product->id;
            $this->invoice_items[$this->pendingProductRowIndex]['product_desciption'] = $product->name;
            $this->invoice_items[$this->pendingProductRowIndex]['unit_price'] = $product->unit_price;
            $this->invoice_items[$this->pendingProductRowIndex]['hsn'] = $product->hsn;
            $this->invoice_items[$this->pendingProductRowIndex]['cgst'] = $product->cgst;
            $this->invoice_items[$this->pendingProductRowIndex]['sgst'] = $product->sgst;
            $this->invoice_items[$this->pendingProductRowIndex]['total_gst'] = $product->total_gst;
            $this->calculateRowTotal($this->pendingProductRowIndex);
        }

        $this->closeAddProductModal();
        $this->dispatch('formResult', type: 'success', message: 'Product natively built and loaded directly into the invoice row element.');
    }

    public function saveCreate()
    {
        $this->organization_id = $this->resolveDefaultOrganizationId();
        $this->validate();
        if (strcasecmp(trim((string) $this->status), 'Approve') === 0) {
            abort(403, 'You cannot approve your own invoice while creating it.');
            $deptId = $this->department_id !== null && $this->department_id !== ''
                ? (int) $this->department_id
                : null;
            abort_unless(
                InvoiceDepartmentAuthorization::canApproveInvoice(auth()->user(), $this->organization_id, $deptId, null),
                403
            );
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            $generatedInvoiceNumber = $this->invoice()->nextInvoiceNumberForOrganization((int) $this->organization_id);

            $invoice = $this->invoice()->createInvoice([
                'invoice_number' => $generatedInvoiceNumber,
                'organization_id' => $this->organization_id,
                'outlet_id' => $this->outlet_id,
                'vendor_id' => $this->vendor_id,
                'createdby_id' => auth()->id(),
                'department_id' => $this->department_id,
                'pay_term' => $this->pay_term,
                'comp_date' => $this->comp_date ?: null,
                'year' => $this->year,
                'description' => $this->description,
                'total_amount' => $this->total_amount ?? 0,
                'paid_amount' => $this->paid_amount ?? 0,
                'status' => $this->status,
                'priority' => $this->priority,
            ]);

            InvoiceStatusHistory::query()->create([
                'invoice_id' => (int) $invoice->id,
                'user_id' => (int) auth()->id(),
                'from_status' => null,
                'to_status' => $this->normalizeStatus((string) $invoice->status),
                'comment' => 'Invoice created.',
                'created_at' => now(),
            ]);

            $this->invoice()->syncInvoiceDetails($invoice, $this->invoice_items);

            $this->processFileUploads($invoice);
        });

        $this->dispatch('formResult', type: 'success', message: 'Invoice created successfully.');
        $this->closeModals();
    }

    public function saveEdit()
    {
        if (empty($this->organization_id) && $this->editId) {
            $this->organization_id = $this->invoice()->getOrganizationIdByInvoiceId($this->editId);
        }

        $this->validate();
        $invoice = $this->invoice()->findForEdit($this->editId);
        $previousStatus = $this->normalizeStatus((string) $invoice->status);
        if (strcasecmp(trim((string) $this->status), 'Approve') === 0) {
            abort_if(
                (int) $invoice->createdby_id === (int) auth()->id(),
                403,
                'You cannot approve your own invoice.'
            );
            $deptId = $this->department_id !== null && $this->department_id !== ''
                ? (int) $this->department_id
                : null;
            abort_unless(
                InvoiceDepartmentAuthorization::canApproveInvoice(
                    auth()->user(),
                    (int) ($this->organization_id ?? $invoice->organization_id),
                    $deptId,
                    $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
                ),
                403
            );
        }
        $this->invoice()->updateInvoice($invoice, [
            'invoice_number' => $this->invoice_number,
            'organization_id' => $this->organization_id,
            'outlet_id' => $this->outlet_id,
            'vendor_id' => $this->vendor_id,
            'department_id' => $this->department_id,
            'pay_term' => $this->pay_term,
            'comp_date' => $this->comp_date ?: null,
            'year' => $this->year,
            'description' => $this->description,
            'total_amount' => $this->total_amount ?? 0,
            'paid_amount' => $this->paid_amount ?? 0,
            'status' => $this->status,
            'priority' => $this->priority,
        ]);

        $newStatus = $this->normalizeStatus((string) $this->status);
        if ($previousStatus !== $newStatus) {
            InvoiceStatusHistory::query()->create([
                'invoice_id' => (int) $invoice->id,
                'user_id' => (int) auth()->id(),
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'comment' => 'Status updated from invoice edit form.',
                'created_at' => now(),
            ]);
        }

        $this->invoice()->syncInvoiceDetails($invoice, $this->invoice_items);

        $this->processFileUploads($invoice);

        $this->dispatch('formResult', type: 'success', message: 'Invoice updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id)
    {
        abort_unless(auth()->user()->can('delete-invoice'), 403);

        $record = Invoice::query()->findOrFail($id);
        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $record->organization_id,
                $record->department_id !== null ? (int) $record->department_id : null,
                $record->createdby_id !== null ? (int) $record->createdby_id : null
            ),
            403
        );

        $this->invoice()->deleteInvoice($id);
        $this->dispatch('deleteResult', success: true, message: 'Invoice deleted successfully.');
    }

    public function closeModals()
    {
        $this->showCreateModal = $this->showEditModal = $this->showViewModal = false;
        $this->closeApproveModal();
        $this->closePaymentHistoryModal();
        $this->closePaymentModal();
        $this->resetForm();
    }

    public function backFromForm()
    {
        $this->closeModals();
    }

    private function resetForm()
    {
        $this->resetValidation();
        $this->reset(['invoice_number', 'outlet_id', 'vendor_id', 'department_id', 'pay_term', 'comp_date', 'year', 'description', 'total_amount', 'paid_amount', 'editId', 'gross_total', 'tax_total', 'uploaded_files', 'approveInvoiceId', 'approve_comment']);
        $this->status = 'Pending';
        $this->priority = 'Medium';
        $this->existing_files = [];
        $this->organization_id = $this->resolveDefaultOrganizationId();
        $this->resetLineItems();
        $this->calculateGrandTotal();
    }

    public function render()
    {
        $orgId = $this->organization_id;
        $user = auth()->user();
        $ownInvoicesOnly = InvoiceDepartmentAuthorization::listOwnInvoicesOnly($user, $orgId);
        $listDeptRestriction = InvoiceDepartmentAuthorization::mergedListDepartmentRestriction($user, $orgId);
        $listDeptRestrictionForQuery = $ownInvoicesOnly ? null : $listDeptRestriction;

        $dropdowns = $this->invoice()->getOrganizationDropdownData($this->organization_id);
        $vendors = $dropdowns['vendors'];
        $departments = $dropdowns['departments'];
        if (is_array($listDeptRestriction)) {
            if ($listDeptRestriction === []) {
                $departments = collect();
            } else {
                $departments = $departments->whereIn('id', $listDeptRestriction);
            }
        }
        $outlets = $dropdowns['outlets'];
        $locations = $dropdowns['locations'];
        $products = $dropdowns['products'];
        $filterDepartments = $ownInvoicesOnly
            ? collect()
            : $this->invoice()->getFilterDepartments($this->organization_id, $listDeptRestriction);

        [$invoicePeriodStart, $invoicePeriodEnd] = $this->resolveCreatedAtRangeForQuery();

        return view('invoice.livewire.invoices', [
            'invoices' => $this->invoice()->paginateForList(
                $this->organization_id,
                $this->search,
                $this->filterStatuses,
                $ownInvoicesOnly ? [] : $this->filterDepartmentIds,
                $this->perPage,
                $listDeptRestrictionForQuery,
                $ownInvoicesOnly,
                $invoicePeriodStart,
                $invoicePeriodEnd,
            ),
            'invoicePeriodStart' => $invoicePeriodStart,
            'invoicePeriodEnd' => $invoicePeriodEnd,
            'vendors' => $vendors,
            'departments' => $departments,
            'filterDepartments' => $filterDepartments,
            'outlets' => $outlets,
            'locations' => $locations,
            'products' => $products,
            'viewRecord' => $this->invoice()->findForView($this->viewId),
        ]);
    }

    private function processFileUploads($invoice)
    {
        if (!empty($this->uploaded_files)) {
            $rootDir = public_path('invoice_files');
            $baseDir = $rootDir . '/' . $invoice->id;

            $this->ensureInvoiceDirectoryExists($rootDir);
            $this->ensureInvoiceDirectoryExists($baseDir);

            foreach ($this->uploaded_files as $file) {
                $originalName = $file->getClientOriginalName();
                $filename = $originalName;
                $counter = 1;
                $destinationPath = $baseDir . '/' . $filename;

                while (File::exists($destinationPath)) {
                    $filename = time() . '_' . $counter . '_' . $originalName;
                    $destinationPath = $baseDir . '/' . $filename;
                    $counter++;
                }

                File::put($destinationPath, File::get($file->getRealPath()));
                @chmod($destinationPath, 0644);
                
                $this->invoice()->createInvoiceFile([
                    'invoice_id' => $invoice->id,
                    'filename' => $filename,
                    'created_at' => now(),
                ]);
            }
            $this->uploaded_files = [];
            $this->refreshExistingFiles($invoice->id);
        }
    }

    public function deleteFile($fileId)
    {
        $fileRec = $this->invoice()->findInvoiceFile((int) $fileId);
        if ($fileRec) {
            $filePath = public_path('invoice_files/' . $fileRec->invoice_id . '/' . $fileRec->filename);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $this->invoice()->deleteInvoiceFile($fileRec);
            $this->refreshExistingFiles($fileRec->invoice_id);
        }
    }

    public function removeUpload($index)
    {
        if(isset($this->uploaded_files[$index])) {
            unset($this->uploaded_files[$index]);
            $this->uploaded_files = array_values($this->uploaded_files);
        }
    }

    private function refreshExistingFiles(int $invoiceId): void
    {
        $this->existing_files = $this->invoice()->listInvoiceFilesAsArray($invoiceId);
    }

    private function ensureInvoiceDirectoryExists(string $path): void
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true, true);
        }

        @chmod($path, 0775);
    }

    private function normalizeStatus(?string $status): string
    {
        $value = strtolower(trim((string) $status));

        return match ($value) {
            'approve', 'approved' => 'Approve',
            'in process', 'in_process', 'processing' => 'in_process',
            'complete', 'completed' => 'Complete',
            default => 'Pending',
        };
    }

    public function canApproveRow(Invoice $invoice): bool
    {
        if (! auth()->user()?->can('approve-invoice')) {
            return false;
        }

        if ((int) $invoice->createdby_id === (int) auth()->id()) {
            return false;
        }

        if ($this->normalizeStatus((string) $invoice->status) === 'Approve') {
            return false;
        }

        return InvoiceDepartmentAuthorization::canApproveInvoice(
            auth()->user(),
            (int) $invoice->organization_id,
            $invoice->department_id !== null ? (int) $invoice->department_id : null,
            $invoice->createdby_id !== null ? (int) $invoice->createdby_id : null
        );
    }

    private function resolveDefaultOrganizationId(): ?int
    {
        $sessionOrganizationId = session('current_organization_id');
        if (!empty($sessionOrganizationId)) {
            return (int) $sessionOrganizationId;
        }

        $user = auth()->user();
        if ($user && !empty($user->last_selected_organization_id)) {
            return (int) $user->last_selected_organization_id;
        }

        return null;
    }
}
