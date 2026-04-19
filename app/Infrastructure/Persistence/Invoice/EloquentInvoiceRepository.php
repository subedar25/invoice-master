<?php

namespace App\Infrastructure\Persistence\Invoice;

use App\Core\Invoice\Contracts\InvoiceRepository;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceFile;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentInvoiceRepository implements InvoiceRepository
{
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
        $query = Invoice::query()
            ->with(['vendor', 'organization', 'department', 'outlet']);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($createdFrom !== null && $createdTo !== null) {
            $query->where('created_at', '>=', $createdFrom)
                ->where('created_at', '<=', $createdTo);
        }

        if ($ownInvoicesOnly && auth()->check()) {
            $query->where('createdby_id', (int) auth()->id());
        }

        $effectiveDeptIds = $this->effectiveDepartmentIds($restrictDepartmentIds, $filterDepartmentIds);
        if ($effectiveDeptIds === []) {
            $query->whereRaw('1 = 0');
        } elseif ($effectiveDeptIds !== null) {
            $query->whereIn('department_id', $effectiveDeptIds);
        }

        $search = trim($search);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('invoice_number', 'like', $like)
                    ->orWhereHas('vendor', function (Builder $vq) use ($like) {
                        $vq->where('name', 'like', $like);
                    })
                    ->orWhereHas('outlet', function (Builder $oq) use ($like) {
                        $oq->where('name', 'like', $like);
                    });
            });
        }

        $this->applyStatusesFilter($query, $filterStatuses);

        return $query->latest()->paginate($perPage);
    }

    public function findForEdit(int $id): Invoice
    {
        return Invoice::query()->with(['details', 'files'])->findOrFail($id);
    }

    public function findForView(int $id): ?Invoice
    {
        return Invoice::query()
            ->with([
                'vendor',
                'details',
                'organization',
                'department',
                'outlet',
                'statusHistories' => fn ($q) => $q->latest('id'),
                'statusHistories.user',
            ])
            ->find($id);
    }

    public function getOrganizationIdByInvoiceId(int $invoiceId): ?int
    {
        $value = Invoice::query()->whereKey($invoiceId)->value('organization_id');

        return $value !== null ? (int) $value : null;
    }

    public function findOrganization(?int $id): ?Organization
    {
        if ($id === null) {
            return null;
        }

        return Organization::query()->find($id);
    }

    public function lockLatestInvoiceByNumberPrefix(int $organizationId, string $prefix): ?Invoice
    {
        return Organization::query()
            ->whereKey($organizationId)
            ->first()
            ?->invoices()
            ->where('invoice_number', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    public function createInvoice(array $attributes): Invoice
    {
        return Invoice::query()->create($attributes);
    }

    public function updateInvoice(Invoice $invoice, array $attributes): void
    {
        $invoice->update($attributes);
    }

    public function deleteInvoice(int $id): void
    {
        Invoice::destroy($id);
    }

    public function syncInvoiceDetails(Invoice $invoice, array $lineItems): void
    {
        $invoice->details()->delete();
        $productIds = array_values(array_unique(array_filter(array_map(
            static fn (array $item): ?int => isset($item['product_id']) && $item['product_id'] !== ''
                ? (int) $item['product_id']
                : null,
            $lineItems
        ))));

        $snapshotByProductId = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'name', 'hsn'])
            ->keyBy('id');

        foreach ($lineItems as $item) {
            $productId = isset($item['product_id']) && $item['product_id'] !== '' ? (int) $item['product_id'] : null;
            $snapshot = $productId !== null ? $snapshotByProductId->get($productId) : null;

            $item['product_name'] = trim((string) ($item['product_name'] ?? $item['product_desciption'] ?? $snapshot?->name ?? '')) ?: null;
            $item['product_desciption'] = trim((string) ($item['product_desciption'] ?? $item['product_name'] ?? $snapshot?->name ?? '')) ?: null;
            $item['hsn'] = trim((string) ($item['hsn'] ?? $snapshot?->hsn ?? '')) ?: null;

            $invoice->details()->create($item);
        }
    }

    public function getDepartmentsForFilterDropdown(?int $organizationId, ?array $restrictToIds = null): Collection
    {
        if (! $organizationId) {
            return new Collection();
        }

        $q = Department::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name');

        if (is_array($restrictToIds)) {
            if ($restrictToIds === []) {
                return new Collection();
            }
            $q->whereIn('id', array_map('intval', $restrictToIds));
        }

        return $q->get(['id', 'name']);
    }

    /**
     * @param  array<int>|null  $restrictDepartmentIds
     * @param  array<int, int|string>  $filterDepartmentIds
     * @return null|array<int> null = no department filter; [] = impossible / no rows
     */
    private function effectiveDepartmentIds(?array $restrictDepartmentIds, array $filterDepartmentIds): ?array
    {
        $filter = array_values(array_unique(array_filter(array_map('intval', $filterDepartmentIds))));

        if ($restrictDepartmentIds === null) {
            return $filter === [] ? null : $filter;
        }

        if ($restrictDepartmentIds === []) {
            return [];
        }

        if ($filter === []) {
            return array_values(array_unique($restrictDepartmentIds));
        }

        $intersection = array_values(array_intersect($restrictDepartmentIds, $filter));

        return $intersection;
    }

    public function getOrganizationDropdownData(?int $organizationId): array
    {
        $empty = [
            'vendors' => new Collection(),
            'departments' => new Collection(),
            'outlets' => new Collection(),
            'locations' => new Collection(),
            'products' => new Collection(),
        ];

        if (!$organizationId) {
            return $empty;
        }

        $org = Organization::query()->find($organizationId);
        if (!$org) {
            return $empty;
        }

        return [
            'vendors' => $org->vendors()->active()->get(),
            'departments' => $org->departments()->get(),
            'outlets' => $org->outlets()->where('status', 1)->get(),
            'locations' => Location::query()->where('organization_id', $organizationId)->get(),
            'products' => $org->products()->where('status', 1)->get(),
        ];
    }

    public function findProductForOrganization(int $productId, ?int $organizationId): ?Product
    {
        $query = Product::query()->whereKey($productId);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->first();
    }

    public function createOutlet(array $attributes): Outlet
    {
        return Outlet::query()->create($attributes);
    }

    public function createVendor(array $attributes): Vendor
    {
        return Vendor::query()->create($attributes);
    }

    public function createProduct(array $attributes): Product
    {
        return Product::query()->create($attributes);
    }

    public function createInvoiceFile(array $attributes): InvoiceFile
    {
        return InvoiceFile::query()->create($attributes);
    }

    public function findInvoiceFile(int $id): ?InvoiceFile
    {
        return InvoiceFile::query()->find($id);
    }

    public function deleteInvoiceFile(InvoiceFile $file): void
    {
        $file->delete();
    }

    public function listInvoiceFilesForInvoice(int $invoiceId): Collection
    {
        return InvoiceFile::query()
            ->where('invoice_id', $invoiceId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array<int, string>  $filterStatuses
     */
    private function applyStatusesFilter(Builder $query, array $filterStatuses): void
    {
        $statuses = array_values(array_unique(array_filter(array_map(static fn ($s) => strtolower(trim((string) $s)), $filterStatuses))));
        if ($statuses === []) {
            return;
        }

        $query->where(function (Builder $outer) use ($statuses) {
            foreach ($statuses as $status) {
                $outer->orWhere(function (Builder $sub) use ($status) {
                    $this->applyOneStatusCondition($sub, $status);
                });
            }
        });
    }

    private function applyOneStatusCondition(Builder $query, string $status): void
    {
        $status = strtolower(trim($status));

        if ($status === 'approve') {
            $query->whereIn('status', ['Approve', 'approved', 'Approved']);

            return;
        }

        if ($status === 'pending') {
            $query->whereIn('status', ['Pending', 'pending']);

            return;
        }

        if ($status === 'in_process') {
            $query->whereIn('status', ['in_process', 'In Process', 'in process', 'processing']);

            return;
        }

        if ($status === 'complete') {
            $query->whereIn('status', ['Complete', 'completed', 'Completed']);
        }
    }
}
