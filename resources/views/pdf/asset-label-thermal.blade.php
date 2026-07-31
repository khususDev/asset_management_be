<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Asset Label Thermal</title>
    <style>
        /* Set margin kertas 0 agar tidak memakan space 25mm */
        @page {
            margin: 0;
            size: 141.73pt 70.86pt;
            /* Ukuran 50mm x 25mm */
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 1.5mm;
            /* Padding tipis di sekeliling stiker */
            background-color: #fff;
        }

        /* Container 1 Stiker Thermal */
        .thermal-card {
            width: 100%;
            height: 22mm;
            /* Di-set 22mm agar aman dari batas 25mm */
            overflow: hidden;
            page-break-after: always;
            /* 1 stiker = 1 lembar thermal */
        }

        .thermal-card:last-child {
            page-break-after: avoid;
        }

        .header {
            font-size: 5pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 0.5px solid #000;
            padding-bottom: 1px;
            margin-bottom: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-transform: uppercase;
            line-height: 1;
        }

        .asset-name {
            font-size: 5.5pt;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 1px;
            line-height: 1;
        }

        .code-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .qr-td {
            width: 30%;
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .barcode-td {
            width: 70%;
            vertical-align: middle;
            text-align: center;
            padding-left: 1px;
        }

        /* Ukuran QR Code dipadatkan agar tidak offscreen */
        .qr-img {
            width: 9.5mm;
            height: 9.5mm;
            display: block;
            margin: 0 auto;
        }

        /* Ukuran Barcode dipadatkan */
        .barcode-img {
            width: 100%;
            height: 5.5mm;
            display: block;
            margin: 0 auto;
        }

        .asset-code {
            font-size: 5pt;
            font-family: monospace;
            font-weight: bold;
            margin-top: 1px;
            letter-spacing: 0.3px;
            line-height: 1;
        }

        .footer-info {
            font-size: 4.2pt;
            color: #000;
            text-align: center;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            border-top: 0.5px solid #000;
            padding-top: 1px;
            line-height: 1;
        }
    </style>
</head>

<body>

    @foreach ($assets as $item)
        <div class="thermal-card">
            <div class="header">PROPERTY OF AMS</div>
            <div class="asset-name">{{ Str::limit($item->asset_name ?? '-', 22) }}</div>

            <table class="code-table">
                <tr>
                    <!-- QR Code (Kiri) -->
                    <td class="qr-td">
                        <img src="data:image/svg+xml;base64,{{ $item->qrcode_base64 }}" class="qr-img" />
                    </td>
                    <!-- Barcode + Kode Asset (Kanan) -->
                    <td class="barcode-td">
                        <img src="data:image/png;base64,{{ $item->barcode_base64 }}" class="barcode-img" />
                        <div class="asset-code">{{ $item->asset_code ?? ($item->code ?? 'NO-CODE') }}</div>
                    </td>
                </tr>
            </table>

            <div class="footer-info">
                S/N: {{ $item->serial_number ?? '-' }} | Loc: {{ $item->location->name ?? '-' }}
            </div>
        </div>
    @endforeach

</body>

</html>
