<?php

namespace App\Core\Invoice\Services;

use App\Core\Invoice\Contracts\InvoiceRepository;
use App\Models\Invoice;
use App\Models\InvoiceFile;
use App\Models\Organization;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InvoiceService
{
    public function __construct(
        private InvoiceRepository $invoices
    ) {}

    /**
     * @param  array<int, string>  $filterStatuses
     * @param  array<int, int|string>  $filterDepartmentIds
     */
    public function paginateForList(
        ?int $organizationId,
        string $search,
        array $filterStatuses,
        array $filterDepartmentIds,
        int $perPage = 15,
        ?array $restrictDepartmentIds = null,
        bool $ownInvoicesOnly = false,
        ?Carbon $createdFrom = null,
        ?Carbon $createdTo = null,
    ): LengthAwarePaginator {
        return $this->invoices->paginateForList(
            $organizationId,
            $search,
            $filterStatuses,
            $filterDepartmentIds,
            $perPage,
            $restrictDepartmentIds,
            $ownInvoicesOnly,
            $createdFrom,
            $createdTo,
        );
    }

    public function getFilterDepartments(?int $organizationId, ?array $restrictToIds = null): Collection
    {
        return $this->invoices->getDepartmentsForFilterDropdown($organizationId, $restrictToIds);
    }

    /**
     * @return array{vendors: Collection, departments: Collection, outlets: Collection, locations: Collection, products: Collection}
     */
    public function getOrganizationDropdownData(?int $organizationId): array
    {
        return $this->invoices->getOrganizationDropdownData($organizationId);
    }

    public function findForEdit(int $id): Invoice
    {
        return $this->invoices->findForEdit($id);
    }

    public function findForView(?int $id): ?Invoice
    {
        if ($id === null) {
            return null;
        }

        return $this->invoices->findForView($id);
    }

    public function getOrganizationIdByInvoiceId(int $invoiceId): ?int
    {
        return $this->invoices->getOrganizationIdByInvoiceId($invoiceId);
    }

    public function nextInvoiceNumberForOrganization(int $organizationId): string
    {
        $organization = $this->invoices->findOrganization($organizationId);
        $prefix = $organization && $organization->invoice_prefix ? $organization->invoice_prefix : 'INV_';

        $latestInvoice = $this->invoices->lockLatestInvoiceByNumberPrefix($organizationId, $prefix);

        $nextNumber = 1;
        if ($latestInvoice) {
            $lastNumberStr = str_replace($prefix, '', $latestInvoice->invoice_number);
            $nextNumber = (int) $lastNumberStr + 1;
        }

        return $prefix . $nextNumber;
    }

    public function createInvoice(array $attributes): Invoice
    {
        return $this->invoices->createInvoice($attributes);
    }

    public function updateInvoice(Invoice $invoice, array $attributes): void
    {
        $this->invoices->updateInvoice($invoice, $attributes);
    }

    public function syncInvoiceDetails(Invoice $invoice, array $lineItems): void
    {
        $this->invoices->syncInvoiceDetails($invoice, $lineItems);
    }

    public function deleteInvoice(int $id): void
    {
        $this->invoices->deleteInvoice($id);
    }

    public function findProductForOrganization(int $productId, ?int $organizationId): ?Product
    {
        return $this->invoices->findProductForOrganization($productId, $organizationId);
    }

    public function createOutlet(array $attributes): Outlet
    {
        return $this->invoices->createOutlet($attributes);
    }

    public function createVendor(array $attributes): Vendor
    {
        return $this->invoices->createVendor($attributes);
    }

    public function createProduct(array $attributes): Product
    {
        return $this->invoices->createProduct($attributes);
    }

    public function createInvoiceFile(array $attributes): InvoiceFile
    {
        return $this->invoices->createInvoiceFile($attributes);
    }

    public function findInvoiceFile(int $id): ?InvoiceFile
    {
        return $this->invoices->findInvoiceFile($id);
    }

    public function deleteInvoiceFile(InvoiceFile $file): void
    {
        $this->invoices->deleteInvoiceFile($file);
    }

    public function listInvoiceFilesAsArray(int $invoiceId): array
    {
        return $this->invoices->listInvoiceFilesForInvoice($invoiceId)->toArray();
    }

    public function findOrganization(?int $id): ?Organization
    {
        return $this->invoices->findOrganization($id);
    }
}
