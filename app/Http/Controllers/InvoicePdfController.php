<?php

namespace App\Http\Controllers;

use App\Core\Invoice\Contracts\InvoiceRepository;
use App\Core\Invoice\Services\InvoicePdfService;
use App\Support\InvoiceDepartmentAuthorization;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfController extends Controller
{
    public function __invoke(int $invoice, InvoicePdfService $invoicePdfService, InvoiceRepository $invoiceRepository): Response
    {
        abort_unless(auth()->user()?->can('list-invoices'), 403);

        $model = $invoiceRepository->findForView($invoice);
        abort_unless($model, 404);

        abort_unless(
            InvoiceDepartmentAuthorization::canViewInvoice(
                auth()->user(),
                (int) $model->organization_id,
                $model->department_id !== null ? (int) $model->department_id : null,
                $model->createdby_id !== null ? (int) $model->createdby_id : null
            ),
            403
        );

        return $invoicePdfService->stream($model);
    }
}
