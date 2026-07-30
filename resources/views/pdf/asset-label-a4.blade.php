<!DOCTYPE html>
<html>

<head>
    @php

        use Endroid\QrCode\Builder\Builder;
        use Endroid\QrCode\Writer\PngWriter;

    @endphp
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm 4mm;
        }

        td {
            width: 33.33%;
            height: 33mm;
            vertical-align: top;
        }

        .label {

            width: 100%;
            height: 100%;

            border: 1px solid #444;
            border-radius: 6px;

            padding: 6px;

            overflow: hidden;

        }

        .header {

            text-align: center;
            font-size: 9px;
            font-weight: bold;

            border-bottom: 1px solid #ddd;

            padding-bottom: 3px;

            margin-bottom: 4px;

        }

        .content {

            display: flex;

        }

        .qr {

            width: 34%;
            text-align: center;

        }

        .qr img {

            width: 72px;
            height: 72px;

        }

        .info {

            width: 66%;
            padding-left: 6px;

        }

        .code {

            font-weight: bold;
            font-size: 10px;

            margin-bottom: 4px;
        }

        .name {

            font-size: 9px;
            margin-bottom: 3px;

        }

        .category {

            color: #666;
            font-size: 8px;

        }

        .footer {

            margin-top: 5px;

            border-top: 1px solid #eee;

            padding-top: 3px;

            font-size: 7px;

            color: #777;

            text-align: center;

        }
    </style>

</head>

<body>

    <table>

        @foreach ($assets->chunk(3) as $rows)
            <tr>

                @foreach ($rows as $asset)
                    <td>

                        <div class="label">

                            <div class="header">

                                PT. MERINDO MAKMUR

                            </div>

                            <div class="content">

                                <div class="qr">
                                    @php

                                        $qr = Builder::create()
                                            ->writer(new PngWriter())
                                            ->data($asset->asset_code)
                                            ->size(170)
                                            ->margin(0)
                                            ->build();

                                    @endphp

                                    <img src="data:image/png;base64,{{ base64_encode($qr->getString()) }}"
                                        style="width:72px;height:72px;">
                                </div>

                                <div class="info">

                                    <div class="code">

                                        {{ $asset->asset_code }}

                                    </div>

                                    <div class="name">

                                        {{ \Illuminate\Support\Str::limit($asset->asset_name, 40) }}

                                    </div>

                                    <div class="category">

                                        {{ $asset->category?->name ?? '-' }}

                                    </div>

                                    @if ($asset->brand)
                                        <div>

                                            Brand :
                                            {{ $asset->brand?->name }}

                                        </div>
                                    @endif

                                    @if ($asset->model)
                                        <div>

                                            Model :
                                            {{ $asset->model->name }}

                                        </div>
                                    @endif

                                </div>

                            </div>

                            <div class="footer">

                                Property of PT. Merindo Makmur

                            </div>

                        </div>

                    </td>
                @endforeach

                @if ($rows->count() < 3)
                    @for ($i = $rows->count(); $i < 3; $i++)
                        <td></td>
                    @endfor
                @endif

            </tr>
        @endforeach

    </table>

</body>

</html>
