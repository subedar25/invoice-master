<?php

namespace App\Http\Livewire\Invoice;

use App\Models\Invoice;
use App\Models\Vendor;
use App\Models\Organization;
use App\Models\Department;
use App\Models\InvoiceFile;
use App\Models\Outlet;
use App\Models\Location;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\File;

class Invoices extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showViewModal = false;
    public bool $showAddOutletModal = false;
    public bool $showAddVendorModal = false;
    public bool $showAddProductModal = false;

    public ?int $editId = null;
    public ?int $viewId = null;

    // Form fields
    public $invoice_number, $organization_id, $vendor_id, $department_id, $outlet_id, $pay_term, $comp_date, $created_date, $year, $description, $total_amount, $paid_amount;
    public $status = 'pending';
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
            'invoice_items' => ['required', 'array', 'min:1'],
            'invoice_items.*.product_desciption' => ['required', 'string'],
            'invoice_items.*.quantity' => ['required', 'numeric', 'min:1'],
            'invoice_items.*.unit_price' => ['required', 'numeric'],
            'uploaded_files' => ['nullable', 'array'],
            'uploaded_files.*' => ['file', 'max:10240'],
        ];
    }

    public function mount()
    {
        $this->resetLineItems();
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
        $product = \App\Models\Product::find($productId);
        if ($product) {
            $this->invoice_items[$index]['product_id'] = $product->id;
            $this->invoice_items[$index]['product_desciption'] = $product->name;
            $this->invoice_items[$index]['unit_price'] = $product->unit_price ?? 0;
            $this->invoice_items[$index]['hsn'] = $product->hsn ?? '';
            $this->invoice_items[$index]['cgst'] = $product->cgst ?? 0;
            $this->invoice_items[$index]['sgst'] = $product->sgst ?? 0;

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
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $this->editId = $id;
        $record = Invoice::with(['details', 'files'])->findOrFail($id);
        
        $this->organization_id = $record->organization_id;
        $this->invoice_number = $record->invoice_number;
        $this->organization_id = $record->organization_id;
        $this->outlet_id = $record->outlet_id;
        $this->vendor_id = $record->vendor_id;
        $this->department_id = $record->department_id;
        $this->pay_term = $record->pay_term;
        $this->comp_date = $record->comp_date;
        $this->created_date = $record->created_date;
        $this->year = $record->year;
        $this->description = $record->description;
        $this->total_amount = number_format((float)$record->total_amount, 2, '.', '');
        $this->paid_amount = $record->paid_amount;
        $this->status = $record->status;
        $this->priority = $record->priority ?? 'Medium';
        $this->invoice_items = $record->details->toArray();
        $this->existing_files = $record->files->toArray();
        $this->showEditModal = true;
        
        // Auto-run totals to populate grid safely on edit open
        $this->calculateGrandTotal();
    }

    public function openViewModal(int $id)
    {
        $this->viewId = $id;
        $this->showViewModal = true;
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

        $outlet = Outlet::create([
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

        $vendor = \App\Models\Vendor::create([
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

        $product = Product::create([
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

    protected function generateInvoiceNumber($organizationId)
    {
        $organization = Organization::find($organizationId);
        $prefix = $organization && $organization->invoice_prefix ? $organization->invoice_prefix : 'INV_';
        
        $latestInvoice = $organization ? $organization->invoices()
            ->where('invoice_number', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first() : null;
            
        $nextNumber = 1;
        if ($latestInvoice) {
            $lastNumberStr = str_replace($prefix, '', $latestInvoice->invoice_number);
            $nextNumber = (int)$lastNumberStr + 1;
        }

        return $prefix . $nextNumber;
    }

    public function saveCreate()
    {
        $this->validate();

        \Illuminate\Support\Facades\DB::transaction(function () {
            $generatedInvoiceNumber = $this->generateInvoiceNumber($this->organization_id);

            $invoice = Invoice::create([
                'invoice_number' => $generatedInvoiceNumber,
                'organization_id' => $this->organization_id,
                'outlet_id' => $this->outlet_id,
                'vendor_id' => $this->vendor_id,
                'createdby_id' => auth()->id(),
                'department_id' => $this->department_id,
                'pay_term' => $this->pay_term,
                'comp_date' => $this->comp_date,
                'created_date' => $this->created_date,
                'year' => $this->year,
                'description' => $this->description,
                'total_amount' => $this->total_amount ?? 0,
                'paid_amount' => $this->paid_amount ?? 0,
                'status' => $this->status,
                'priority' => $this->priority,
            ]);

            foreach ($this->invoice_items as $item) {
                $invoice->details()->create($item);
            }
            
            $this->processFileUploads($invoice);
        });

        $this->dispatch('formResult', type: 'success', message: 'Invoice created successfully.');
        $this->closeModals();
    }

    public function saveEdit()
    {
        $this->validate();
        $invoice = Invoice::findOrFail($this->editId);
        $invoice->update([
            'invoice_number' => $this->invoice_number,
            'organization_id' => $this->organization_id,
            'outlet_id' => $this->outlet_id,
            'vendor_id' => $this->vendor_id,
            'department_id' => $this->department_id,
            'pay_term' => $this->pay_term,
            'comp_date' => $this->comp_date,
            'created_date' => $this->created_date,
            'year' => $this->year,
            'description' => $this->description,
            'total_amount' => $this->total_amount ?? 0,
            'paid_amount' => $this->paid_amount ?? 0,
            'status' => $this->status,
            'priority' => $this->priority,
        ]);

        $invoice->details()->delete();
        foreach ($this->invoice_items as $item) {
            $invoice->details()->create($item);
        }
        
        $this->processFileUploads($invoice);

        $this->dispatch('formResult', type: 'success', message: 'Invoice updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id)
    {
        Invoice::destroy($id);
        $this->dispatch('deleteResult', success: true, message: 'Invoice deleted successfully.');
    }

    public function closeModals()
    {
        $this->showCreateModal = $this->showEditModal = $this->showViewModal = false;
        $this->resetForm();
    }

    public function backFromForm()
    {
        $this->closeModals();
    }

    private function resetForm()
    {
        $this->resetValidation();
        $this->reset(['invoice_number', 'organization_id', 'outlet_id', 'vendor_id', 'department_id', 'pay_term', 'comp_date', 'created_date', 'year', 'description', 'total_amount', 'paid_amount', 'editId', 'gross_total', 'tax_total', 'uploaded_files']);
        $this->status = 'pending';
        $this->priority = 'Medium';
        $this->existing_files = [];
        $this->resetLineItems();
        $this->calculateGrandTotal();
    }

    public function render()
    {
        $vendors = collect();
        $departments = collect();
        $outlets = collect();
        $locations = collect();
        $products = collect();

        if ($this->organization_id) {
            $org = Organization::find($this->organization_id);
            if ($org) {
                $vendors = $org->vendors()->active()->get();
                $departments = $org->departments()->get();
                $outlets = $org->outlets()->where('status', 1)->get();
                $locations = Location::where('organization_id', $this->organization_id)->get();
                $products = $org->products()->where('status', 1)->get();
            }
        }

        return view('invoice.livewire.invoices', [
            'invoices' => Invoice::with(['vendor', 'organization', 'department', 'outlet'])->paginate(15),
            'vendors' => $vendors,
            'organizations' => Organization::all(),
            'departments' => $departments,
            'outlets' => $outlets,
            'locations' => $locations,
            'products' => $products,
            'viewRecord' => $this->viewId ? Invoice::with(['vendor', 'details', 'organization', 'department', 'outlet'])->find($this->viewId) : null
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
                
                InvoiceFile::create([
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
        $fileRec = InvoiceFile::find($fileId);
        if ($fileRec) {
            $filePath = public_path('invoice_files/' . $fileRec->invoice_id . '/' . $fileRec->filename);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $fileRec->delete();
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
        $this->existing_files = InvoiceFile::where('invoice_id', $invoiceId)
            ->orderByDesc('id')
            ->get()
            ->toArray();
    }

    private function ensureInvoiceDirectoryExists(string $path): void
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true, true);
        }

        @chmod($path, 0775);
    }
}
