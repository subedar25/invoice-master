<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PO {{ $invoice->invoice_number ?? '—' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px 0; }
        .muted { color: #555; font-size: 10px; }
        .row { width: 100%; margin-bottom: 16px; }
        .row:after { content: ''; display: table; clear: both; }
        .col { float: left; width: 48%; }
        .col-right { float: right; width: 48%; text-align: right; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.items th, table.items td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        table.items th { background: #f3f3f3; font-weight: bold; }
        .text-right { text-align: right; }
        .totals { margin-top: 12px; width: 100%; }
        .totals td { padding: 4px 0; }
        .totals .label { text-align: right; padding-right: 12px; width: 80%; }
        .section-title { font-weight: bold; margin: 12px 0 6px 0; font-size: 12px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .org-block { margin-bottom: 16px; }
        .vendor-contact-line { margin-top: 8px; font-size: 10px; line-height: 1.5; }
        .vendor-contact-line span { display: inline-block; margin-right: 16px; }
    </style>
</head>
<body>
    <div class="row">
        <div class="col">
            <h1>PURCHASE ORDER</h1>
            <div class="muted">PO NO# {{ $invoice->invoice_number ?? '—' }}</div>
        </div>
        <div class="col-right">
            <div><strong>Date:</strong>
                @if($invoice->created_at)
                    {{ $invoice->created_at->format('d M Y') }}
                @else
                    —
                @endif
            </div>
            @if($invoice->comp_date)
                <div><strong>Completion date:</strong> {{ $invoice->comp_date->format('d M Y') }}</div>
            @endif
            @if($invoice->pay_term)
                <div><strong>Pay term:</strong><br>{!! nl2br(e($invoice->pay_term)) !!}</div>
            @endif
        </div>
    </div>

    <div class="org-block">
        <div class="row">
            <div class="col">
                <strong>{{ $invoice->organization?->name ?? '—' }}</strong>
                @if($invoice->organization?->address)
                    <div class="muted" style="margin-top: 4px; white-space: pre-line;">{!! nl2br(e($invoice->organization->address)) !!}</div>
                @endif
            </div>
            <div class="col-right">
                <div class="section-title" style="margin-top: 0; border-bottom: none; padding-bottom: 0;">Outlet Name:</div>
                <strong>{{ $invoice->outlet?->name ?? '—' }}</strong>
                @if($invoice->outlet?->address)
                    <div>{{ $invoice->outlet->address }}</div>
                @endif
                @php
                    $outletCity = array_filter([$invoice->outlet?->city, $invoice->outlet?->pincode]);
                @endphp
                @if(count($outletCity))
                    <div>{{ implode(', ', $outletCity) }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="section-title">Vendor Details:</div>
    <div class="row">
        <div class="col">
            @php
                $v = $invoice->vendor;
                $vendorTitle = $v?->companyname ?: ($v?->name ?? '—');
            @endphp
            <strong>{{ $vendorTitle }}</strong>
            @if($v?->companyname && $v?->name && trim((string) $v->companyname) !== trim((string) $v->name))
                <div>{{ $v->name }}</div>
            @endif
            @if($v?->address)
                <div style="margin-top: 4px;">{{ $v->address }}</div>
            @endif
            @php
                $vendorLoc = array_filter([$v?->city, $v?->state, $v?->pin]);
            @endphp
            @if(count($vendorLoc))
                <div>{{ implode(', ', $vendorLoc) }}</div>
            @endif
            <div class="vendor-contact-line">
                <span><strong>VENDOR GST NO:</strong> {{ $v?->gst ?: '—' }}</span>
                <span><strong>CONTACT NO:</strong> {{ $v?->mobile ?: '—' }}</span>
                <span><strong>EMAIL:</strong> {{ $v?->email ?: '—' }}</span>
            </div>
        </div>
        <div class="col-right">
            @if($invoice->department)
                <div><strong>Department:</strong> {{ $invoice->department->name }}</div>
            @endif
            <div><strong>Priority:</strong> {{ $invoice->priority ?? '—' }}</div>
            <div><strong>Status:</strong> {{ $invoice->status ?? '—' }}</div>
            <div><strong>Work Related to Category:</strong> {{ $invoice->vendor?->category?->name ?? '—' }}</div>
            @php
                $reviewHistory = $invoice->statusHistories
                    ->first(fn ($row) => strtolower((string) ($row->to_status ?? '')) === 'approve');
                $reviewedBy = trim((string) (($reviewHistory?->user?->first_name ?? '') . ' ' . ($reviewHistory?->user?->last_name ?? '')));
                if ($reviewedBy === '') {
                    $reviewedBy = $reviewHistory?->user?->email ?? '—';
                }
            @endphp
            <div><strong>Approved By:</strong> {{ $reviewedBy }}</div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th>Description</th>
                <th style="width: 10%;">HSN</th>
                <th class="text-right" style="width: 8%;">Qty</th>
                <th class="text-right" style="width: 12%;">Unit</th>
                <th class="text-right" style="width: 6%;">CGST %</th>
                <th class="text-right" style="width: 6%;">SGST %</th>
                <th class="text-right" style="width: 14%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->product_name ?? $line->product_desciption ?? '—' }}</td>
                    <td>{{ $line->hsn ?? '—' }}</td>
                    <td class="text-right">{{ number_format((float) ($line->quantity ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($line->unit_price ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($line->cgst ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($line->sgst ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($line->total_price ?? $line->total_amount ?? 0), 2) }}</td>
                </tr>
            @endforeach

            @php
                $subtotal = (float) ($invoice->total_amount ?? 0);
                $gstAmount = (float) $invoice->details->sum(function ($line) {
                    $qty = (float) ($line->quantity ?? 0);
                    $unit = (float) ($line->unit_price ?? 0);
                    $cgst = (float) ($line->cgst ?? 0);
                    $sgst = (float) ($line->sgst ?? 0);
                    $lineBase = $qty * $unit;

                    return $lineBase * (($cgst + $sgst) / 100);
                });
                $grandTotal = $subtotal + $gstAmount;

                $toWords = function (int $number) use (&$toWords): string {
                    $ones = [
                        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven',
                        12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen',
                        17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
                    ];
                    $tens = [
                        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
                        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
                    ];

                    if ($number < 20) {
                        return $ones[$number];
                    }

                    if ($number < 100) {
                        $tenPart = intdiv($number, 10);
                        $remainder = $number % 10;

                        return $tens[$tenPart] . ($remainder ? ' ' . $ones[$remainder] : '');
                    }

                    if ($number < 1000) {
                        $hundredsPart = intdiv($number, 100);
                        $remainder = $number % 100;

                        return $ones[$hundredsPart] . ' Hundred' . ($remainder ? ' ' . $toWords($remainder) : '');
                    }

                    if ($number < 1000000) {
                        $thousandsPart = intdiv($number, 1000);
                        $remainder = $number % 1000;

                        return $toWords($thousandsPart) . ' Thousand' . ($remainder ? ' ' . $toWords($remainder) : '');
                    }

                    if ($number < 1000000000) {
                        $millionsPart = intdiv($number, 1000000);
                        $remainder = $number % 1000000;

                        return $toWords($millionsPart) . ' Million' . ($remainder ? ' ' . $toWords($remainder) : '');
                    }

                    $billionsPart = intdiv($number, 1000000000);
                    $remainder = $number % 1000000000;

                    return $toWords($billionsPart) . ' Billion' . ($remainder ? ' ' . $toWords($remainder) : '');
                };

                $amountInWords = $toWords((int) round($grandTotal));
            @endphp

            <tr>
                <td colspan="4"><strong>Amount in words:</strong> {{ $amountInWords }} Only</td>
                <td colspan="3" class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>{{ number_format($subtotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="5" rowspan="2"><strong>REMARK:</strong> {{ $invoice->description ?: '—' }}</td>
                <td colspan="2" class="text-right"><strong>GST:</strong></td>
                <td class="text-right">{{ number_format($gstAmount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"><strong>GRAND TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: right;">
        <strong>{{ $invoice->organization?->name ?? '—' }}</strong>
    </div>
</body>
</html>
