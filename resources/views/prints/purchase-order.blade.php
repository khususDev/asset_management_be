<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Purchase Order {{ $po->po_number }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 30px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        table {
            border-collapse: collapse;
        }

        .company-header {
            width: 100%;
            margin-bottom: 20px;
        }

        .company-header td {
            vertical-align: middle;
        }

        .company-header img {
            max-height: 80px;
        }

        .title-box {
            margin-top: 15px;
            margin-bottom: 20px;
            border: 2px solid #0F172A;
            padding: 10px;
            text-align: center;
        }

        .title-box h1 {
            font-size: 24px;
            letter-spacing: 2px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .vendor-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .vendor-table th {
            background: #0F172A;
            color: white;
            border: 1px solid #ddd;
            padding: 8px;
        }

        .vendor-table td {
            border: 1px solid #ddd;
            padding: 10px;
            vertical-align: top;
        }

        .item-table {
            width: 100%;
            margin-top: 20px;
        }

        .item-table th {
            background: #0F172A;
            color: white;
            border: 1px solid #ddd;
            padding: 8px;
        }

        .item-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .summary {
            width: 35%;
            margin-left: auto;
            margin-top: 20px;
        }

        .summary td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .remarks {
            margin-top: 40px;
        }

        .signature {
            width: 100%;
            margin-top: 80px;
        }

        .signature td {
            text-align: center;
            width: 33%;
        }

        .sign-space {
            height: 80px;
        }

        .footer {
            margin-top: 60px;
            font-size: 11px;
            color: #666;
        }

        .watermark {
            position: fixed;
            top: 45%;
            left: 20%;
            transform: rotate(-30deg);
            font-size: 90px;
            color: rgba(180, 180, 180, 0.15);
            z-index: -1;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="watermark">
        {{ $po->status }}
    </div>

    <table class="company-header">
        <tr>

            <td width="20%">
                @if(!empty($company['logo']))
                <img src="{{ $company['logo'] }}">
                @endif
            </td>

            <td width="80%" class="text-center">
                <h2>{{ $company['name'] ?? '' }}</h2>

                <div>
                    {{ $company['address'] ?? '' }}
                </div>

                <div>
                    {{ $company['city'] ?? '' }}
                </div>

                <div>
                    Tel : {{ $company['phone'] ?? '' }}
                </div>

                <div>
                    Email : {{ $company['email'] ?? '' }}
                </div>

                <div>
                    {{ $company['website'] ?? '' }}
                </div>

                <div>
                    NPWP : {{ $company['npwp'] ?? '' }}
                </div>
            </td>

        </tr>
    </table>

    <div class="title-box">
        <h1>PURCHASE ORDER</h1>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%">PO Number</td>
            <td width="30%">
                {{ $po->po_number }}
            </td>

            <td width="20%">Order Date</td>
            <td width="30%">
                {{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}
            </td>
        </tr>

        <tr>
            <td>PR Number</td>
            <td>
                {{ $po->purchaseRequest->request_number ?? '-' }}
            </td>

            <td>Status</td>
            <td>
                {{ $po->status }}
            </td>
        </tr>

        <tr>
            <td>Department</td>
            <td>
                {{ $po->department->name ?? '-' }}
            </td>

            <td>Expected Delivery</td>
            <td>
                {{ $po->expected_delivery_date
                ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y')
                : '-' }}
            </td>
        </tr>
    </table>

    <table class="vendor-table">
        <tr>
            <th width="50%">
                Vendor Information
            </th>

            <th width="50%">
                Delivery Information
            </th>
        </tr>

        <tr>
            <td>
                <b>{{ $po->vendor->name ?? '-' }}</b>
                <br><br>

                Payment Term :
                {{ $po->paymentTerm->name ?? '-' }}
            </td>

            <td>
                <b>{{ $po->branch->name ?? '-' }}</b>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Description</th>
                <th width="10%">Qty</th>
                <th width="10%">UOM</th>
                <th width="15%">Unit Price</th>
                <th width="15%">Amount</th>
            </tr>
        </thead>

        <tbody>

            @foreach($po->items as $item)
            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    <b>{{ $item->item_description }}</b>

                    @if($item->item_purpose)
                    <br>
                    <small>
                        {{ $item->item_purpose }}
                    </small>
                    @endif
                </td>

                <td class="text-center">
                    {{ $item->quantity }}
                </td>

                <td class="text-center">
                    {{ $item->uom->name ?? '-' }}
                </td>

                <td class="text-right">
                    {{ number_format($item->unit_price,0,',','.') }}
                </td>

                <td class="text-right">
                    {{ number_format($item->total_amount,0,',','.') }}
                </td>
            </tr>
            @endforeach

        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">
                {{ number_format($po->subtotal,0,',','.') }}
            </td>
        </tr>

        <tr>
            <td>PPN</td>
            <td class="text-right">
                {{ number_format($po->ppn_amount,0,',','.') }}
            </td>
        </tr>

        <tr>
            <td class="bold">
                Grand Total
            </td>

            <td class="text-right bold">
                Rp {{ number_format($po->grand_total,0,',','.') }}
            </td>
        </tr>
    </table>

    <div class="remarks">
        <b>Remarks :</b>
        <br><br>

        {{ $po->remarks ?? '-' }}
    </div>

    <table class="signature">
        <tr>
            <td>
                Prepared By
            </td>

            <td>
                Approved By
            </td>

            <td>
                Vendor Acceptance
            </td>
        </tr>

        <tr>
            <td class="sign-space"></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td>
                <b>
                    {{ $po->user->name ?? '-' }}
                </b>
            </td>

            <td>
                <b>
                    {{ $po->approvedBy->name ?? '-' }}
                </b>
            </td>

            <td>
                ___________________
            </td>
        </tr>
        <tr>
            <td>
                Purchasing Staff
            </td>

            <td>
                Purchasing Manager
            </td>

            <td>
                Vendor Stamp & Signature
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated by
        {{ config('app.name') }}
        <br>

        Printed :
        {{ now()->format('d M Y H:i:s') }}
    </div>

</body>

</html>