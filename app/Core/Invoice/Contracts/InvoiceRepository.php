<?php

namespace App\Core\Invoice\Contracts;

use App\Models\Invoice;
use App\Models\InvoiceFile;
use App\Models\Organization;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepository
{
    /**
     * @param  array<int, string>  $filterStatuses  Lowercase keys: approve, pending, in_process, complete. Empty = all statuses.
     * @param  array<int, int|string>  $filterDepartmentIds  Empty = all departments (within restriction).
     * @param  array<int, int|string>  $filterOutletIds  Empty = all outlets.
     * @param  array<int>|null  $restrictCreatedByUserIds  null = no creator restriction; [] = no rows; non-empty = only these creators.
     * @param  array<int>|null  $restrictDepartmentIds  null = no restriction; [] = no rows; non-empty = whitelist merged with filters.
     */
    public function paginateForList(
        ?int $organizationId,
        string $search,
        array $filterStatuses,
        array $filterDepartmentIds,
        array $filterOutletIds,
        int $perPage = 15,
        ?array $restrictCreatedByUserIds = null,
        ?array $restrictDepartmentIds = null,
        bool $ownInvoicesOnly = false,
        ?Carbon $createdFrom = null,
        ?Carbon $createdTo = null,
    ): LengthAwarePaginator;

    public function findForEdit(int $id): Invoice;

    public function findForView(int $id): ?Invoice;

    public function getOrganizationIdByInvoiceId(int $invoiceId): ?int;

    public function findOrganization(?int $id): ?Organization;

    public function lockLatestInvoiceByNumberPrefix(int $organizationId, string $prefix): ?Invoice;

    public function createInvoice(array $attributes): Invoice;

    public function updateInvoice(Invoice $invoice, array $attributes): void;

    public function deleteInvoice(int $id): void;

    public function syncInvoiceDetails(Invoice $invoice, array $lineItems): void;

    public function getDepartmentsForFilterDropdown(?int $organizationId, ?array $restrictToIds = null): Collection;

    /**
     * @return array{vendors: Collection, departments: Collection, outlets: Collection, locations: Collection, products: Collection}
     */
    public function getOrganizationDropdownData(?int $organizationId): array;

    public function findProductForOrganization(int $productId, ?int $organizationId): ?Product;

    public function createOutlet(array $attributes): Outlet;

    public function createVendor(array $attributes): Vendor;

    public function createProduct(array $attributes): Product;

    public function createInvoiceFile(array $attributes): InvoiceFile;

    public function findInvoiceFile(int $id): ?InvoiceFile;

    public function deleteInvoiceFile(InvoiceFile $file): void;

    public function listInvoiceFilesForInvoice(int $invoiceId): Collection;
}
