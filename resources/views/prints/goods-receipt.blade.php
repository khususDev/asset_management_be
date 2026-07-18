<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        Goods Receipt
    </title>

    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            box-sizing: border-box;
        }

        body {
            margin: 25px;
            color: #000;
        }

        h1 {
            margin: 0;
            font-size: 24px;
        }

        h2 {
            margin: 0;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .border {
            border: 1px solid #000;
        }

        .border td,
        .border th {
            border: 1px solid #000;
            padding: 6px;
        }

        .header-table td {
            padding: 3px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mt-30 {
            margin-top: 30px;
        }

        .signature {
            height: 80px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            font-size: 13px;
            text-align: center;
        }

        @media print {

            @page {
                size: A4 portrait;
                margin: 15mm;
            }

        }
    </style>

</head>

<body>

    <table>

        <tr>

            <td width="80">

                {{-- Logo Company --}}
                {{-- <img src="{{ public_path('logo.png') }}" width="70"> --}}

            </td>

            <td>

                <div class="title">

                    GOODS RECEIPT

                </div>

                <div class="subtitle">

                    Asset Management System

                </div>

            </td>

        </tr>

    </table>

    <table class="header-table mt-20">

        <tr>

            <td width="18%">
                GR Number
            </td>

            <td width="32%">
                : {{ $gr->gr_number }}
            </td>

            <td width="18%">
                Receipt Date
            </td>

            <td>
                : {{ $gr->received_date }}
            </td>

        </tr>

        <tr>

            <td>
                PO Number
            </td>

            <td>
                : {{ $gr->purchaseOrder->po_number }}
            </td>

            <td>
                PO Date
            </td>

            <td>
                : {{ $gr->purchaseOrder->order_date }}
            </td>

        </tr>

    </table>

    <h3 class="mt-20">

        Vendor Information

    </h3>

    <table class="header-table">

        <tr>

            <td width="18%">
                Vendor
            </td>

            <td width="32%">
                : {{ $gr->purchaseOrder->vendor->name }}
            </td>

            <td width="18%">
                Department
            </td>

            <td>
                : {{ optional($gr->purchaseOrder->department)->name }}
            </td>

        </tr>

        <tr>

            <td>
                Address
            </td>

            <td>
                : {{ $gr->purchaseOrder->vendor->address }}
            </td>

            <td>
                Branch
            </td>

            <td>
                : {{ optional($gr->purchaseOrder->branch)->name }}
            </td>

        </tr>

    </table>

    <h3 class="mt-20">

        Item Received

    </h3>

    <table class="border">

        <thead>

            <tr>

                <th width="5%">
                    No
                </th>

                <th width="28%">
                    Item Description
                </th>

                <th width="8%">
                    UOM
                </th>

                <th width="10%">
                    PO Qty
                </th>

                <th width="10%">
                    Prev. Rec
                </th>

                <th width="10%">
                    Receive
                </th>

                <th width="10%">
                    Good
                </th>

                <th width="10%">
                    Reject
                </th>

                <th>
                    Remarks
                </th>

            </tr>

        </thead>

        <tbody>

            @php

                $no = 1;

            @endphp

            @foreach ($gr->items as $item)
                <tr>

                    <td class="text-center">

                        {{ $no++ }}

                    </td>

                    <td>

                        {{ $item->purchaseOrderItem->item_description }}

                    </td>

                    <td class="text-center">

                        {{ optional($item->purchaseOrderItem->uom)->name }}

                    </td>

                    <td class="text-right">

                        {{ number_format($item->purchaseOrderItem->quantity, 2) }}

                    </td>

                    <td class="text-right">

                        {{ number_format($item->received_before_qty, 2) }}

                    </td>

                    <td class="text-right">

                        {{ number_format($item->receive_qty, 2) }}

                    </td>

                    <td class="text-right">

                        {{ number_format($item->accepted_qty, 2) }}

                    </td>

                    <td class="text-right">

                        {{ number_format($item->rejected_qty, 2) }}

                    </td>

                    <td>

                        {{ $item->remarks }}

                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

    <div class="mt-20">

        <strong>

            Remarks

        </strong>

        <div
            style="
            border:1px solid #000;
            min-height:70px;
            padding:10px;
        ">

            {{ $gr->remarks }}

        </div>

    </div>

    <div class="mt-20">

        <table>

            <tr>

                <td width="25%">

                    Receipt Status

                </td>

                <td>

                    :

                    {{ strtoupper($gr->status) }}

                </td>

            </tr>

        </table>

    </div>
