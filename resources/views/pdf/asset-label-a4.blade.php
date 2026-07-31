<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Asset Compact Labels</title>
    <style>
        @page {
            margin: 8mm 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Tabel Utama Grid Labels */
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2mm 3mm;
            /* Jarak antar stiker */
            table-layout: fixed;
        }

        .grid-cell {
            width: 25%;
            vertical-align: top;
            padding: 0;
        }

        /* Desain Kartu Stiker */
        .label-card {
            border: 1px dashed #555;
            padding: 3px 4px;
            box-sizing: border-box;
            border-radius: 3px;
            height: 25mm;
            overflow: hidden;
            background-color: #fff;
        }

        .header {
            font-size: 5.5pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 0.5px solid #aaa;
            padding-bottom: 1px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-transform: uppercase;
        }

        .asset-name {
            font-size: 6pt;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        /* Container QR dan Barcode Berdampingan */
        .code-table {
            width: 100%;
            border-collapse: collapse;
        }

        .qr-td {
            width: 32%;
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .barcode-td {
            width: 68%;
            vertical-align: middle;
            text-align: center;
            padding-left: 2px;
        }

        .qr-img {
            width: 13mm;
            height: 13mm;
            display: block;
            margin: 0 auto;
        }

        .barcode-img {
            width: 100%;
            height: 8.5mm;
            display: block;
            margin: 0 auto;
        }

        .asset-code {
            font-size: 5.5pt;
            font-family: monospace;
            font-weight: bold;
            margin-top: 1px;
            letter-spacing: 0.5px;
        }

        .footer-info {
            font-size: 4.8pt;
            color: #333;
            text-align: center;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            border-top: 0.5px solid #eee;
            padding-top: 1px;
        }
    </style>
</head>

<body>

    <table class="grid-table">
        <!-- Membagi array data menjadi grup isi 4 item per baris -->
        @foreach ($assets->chunk(4) as $chunk)
            <tr>
                @foreach ($chunk as $item)
                    <td class="grid-cell">
                        <div class="label-card">
                            <div class="header">PROPERTY OF AMS</div>
                            <div class="asset-name">{{ Str::limit($item->asset_name ?? '-', 22) }}</div>

                            <table class="code-table">
                                <tr>
                                    <!-- QR Code (Kiri) -->
                                    <td class="qr-td">
                                        <img src="data:image/svg+xml;base64,{{ $item->qrcode_base64 }}" class="qr-img" />
                                    </td>
                                    <!-- Barcode + Text Kode (Kanan) -->
                                    <td class="barcode-td">
                                        <img src="data:image/png;base64,{{ $item->barcode_base64 }}"
                                            class="barcode-img" />
                                        <div class="asset-code">{{ $item->asset_code ?? ($item->code ?? 'NO-CODE') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div class="footer-info">
                                S/N: {{ $item->serial_number ?? '-' }} | Loc: {{ $item->location->name ?? '-' }}
                            </div>
                        </div>
                    </td>
                @endforeach

                <!-- Tambahkan cell kosong jika baris terakhir kurang dari 4 item -->
                @if ($chunk->count() < 4)
                    @for ($i = 0; $i < 4 - $chunk->count(); $i++)
                        <td class="grid-cell"></td>
                    @endfor
                @endif
            </tr>
        @endforeach
    </table>

</body>

</html>
