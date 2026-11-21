<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quo_no }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        /* Atur margin halaman sesuai tinggi header & footer */
        @page {
            margin-top: 350px;
            /* tinggi header */
            margin-bottom: 80px;
            /* tinggi footer */
            margin-left: 20px;
            margin-right: 20px;
        }

        /* Header tetap di semua halaman */
        .header-page {
            position: fixed;
            top: -300px;
            left: 0;
            right: 0;
            height: 100px;
            /* harus sama dengan @page margin-top */
            background: white;
            z-index: 100;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #080808;
            padding-bottom: 5px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 2px;
        }

        .logo {
            width: 80px;
            height: auto;
        }

        .header-text h1 {
            margin: 0;
            font-size: 16px;
            color: #0C6401;
        }

        .header-text p {
            margin: 1px 0;
            font-size: 9px;
        }

        .header-text {
            padding-top: 3px;
        }

        /* Footer tetap di semua halaman */
        .footer {
            position: fixed;
            bottom: -100px;
            left: 0;
            right: 0;
            height: 80px;
            /* harus sama dengan @page margin-bottom */
            text-align: center;
            font-size: 10px;
            color: #555;
            border-top: 1px solid #ccc;
            padding: 5px 0;
            background-color: white;
            z-index: 100;
        }

        .footer img {
            height: 15px;
            margin-right: 5px;
            vertical-align: middle;
            padding-top: 7px;
        }

        .pagenum:before {
            content: counter(page);
        }

        /* Konten utama */
        .content {
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
            font-size: 11px;
        }

        .no-border td {
            border: none;
            padding: 2px 4px;
        }

        .info-table,
        .info-table2,
        .info-table3 {
            margin: 6px 0;
            font-size: 11px;
        }

        .info-table td {
            padding: 3px 4px;
            vertical-align: top;
        }

        .info-table .label {
            width: 20%;
            font-weight: bold;
        }

        .info-table .value {
            width: 30%;
        }

        .info-table .right {
            width: 20%;
            font-weight: bold;
        }

        .info-table .right-value {
            width: 30%;
        }

        .info-table2 th,
        .info-table3 th {
            background-color: #0C6401;
            color: #fff;
            font-weight: normal;
            padding: 3px 4px;
        }

        h2,
        h4 {
            margin: 6px 0 4px 0;
            font-size: 14px;
        }

        p {
            margin: 3px 0;
            font-size: 11px;
            line-height: 1.3;
        }

        /* Approval block (supaya tidak ketimpa header/footer) */
        .approval-block {
            page-break-inside: avoid;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header-page">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width:130px;">
                        <?php
                        $path = public_path('template/img/LOGO_Gatra1.png');
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        if (file_exists($path)) {
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        } else {
                            $base64 = '';
                        }
                        ?>
                        <img src="<?php echo $base64; ?>" alt="logo" class="logo" />
                    </td>
                    <td class="text-top">
                        <div class="header-text">
                            <h1>PT. GATRA PERDANA TRUSTRUE</h1>
                            <p>Calibration Test, Consultant, General Supplier, & Digital Agency for your Business</p>
                            <p>Kawasan Komplek Ruko Golden BCI blok T3 No 12 Bengkong Laut, Kecamatan Bengkong, Kota
                                Batam, Kepulauan Riau</p>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- ===== Tambahkan bagian QUOTATION di bawah garis header ===== -->
            <hr style="border: 1px solid #0C6401; margin: 4px 0 6px 0;">

            <h2 class="text-center" style="margin: 2px 0;">QUOTATION</h2>

            <table class="info-table no-border" style="font-size:10px;">
                <tr>
                    <td style="width:50%; vertical-align:top;">
                        <table style="width:100%;">
                            <tr>
                                <td style="width:35%; font-weight:bold;">Customer</td>
                                <td style="width:65%;">: {{ $quotation->customer_name }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Address</td>
                                <td>: {{ $quotation->customer_address }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Attn</td>
                                <td>: {{ $quotation->attention }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Your Reference</td>
                                <td>: {{ $quotation->your_reference ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Terms</td>
                                <td>: {{ $quotation->terms ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:50%; vertical-align:top;">
                        <table style="width:100%;">
                            <tr>
                                <td style="width:35%; font-weight:bold;">Quo No</td>
                                <td style="width:65%;">: {{ $quotation->quo_no }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Date</td>
                                <td>: {{ \Carbon\Carbon::parse($quotation->date)->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Rev</td>
                                <td>: {{ $quotation->rev }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Job No</td>
                                <td>: {{ $quotation->job_no ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <img src="{{ public_path('template/img/LOGO_Gatra1.png') }}" alt="Logo">
        PT. GATRA PERDANA TRUSTRUE | www.gatraperdanatrustrue.com | Page <span class="pagenum"></span>
    </div>

    <!-- Konten -->
    <div class="content">
        <table class="info-table2" style="margin-bottom:20px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price (IDR)</th>
                    <th>Total (IDR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->total_price, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="no-border">
            <tr>
                <td class="text-right" style="width:80%;">Sub Total</td>
                <td class="text-right">{{ number_format($quotation->total_amount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right">Discount</td>
                <td class="text-right">{{ number_format($quotation->discount, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right"><b>Total</b></td>
                <td class="text-right"><b>{{ number_format($quotation->sub_total, 2, ',', '.') }}</b></td>
            </tr>
        </table>

        <h4>Scope of Work</h4>
        <table class="info-table3" style="margin-bottom:20px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Description</th>
                    <th>PT. GPT</th>
                    <th>Client</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->scopes as $i => $scope)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $scope->description }}</td>
                        <td class="text-center">{!! $scope->responsible_pt_gpt ? '&#10004;' : '' !!}</td>
                        <td class="text-center">{!! $scope->responsible_client ? '&#10004;' : '' !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $termsList = $quotation->terms()->get();
        @endphp
        <p><b>Terms and Conditions:</b></p>
        @if ($termsList && $termsList->count() > 0)
            <ol style="padding-left:20px; margin:0 0 20px 0;">
                @foreach ($termsList as $index => $term)
                    <li>{{ $term->description }}</li>
                @endforeach
            </ol>
        @else
            <p style="margin-bottom:20px;">-</p>
        @endif

        <!-- Approval Block -->
        <div class="approval-block">
            <table style="width:100%;" class="no-border">
                <tr>
                    <td style="width:50%; text-align:left;">
                        <p><b>Approval, by</b></p>
                        <br><br>
                        @if (isset($qrCodeBase64) && $qrCodeBase64)
                            <div style="width:100px; height:100px;">
                                <img src="{{ $qrCodeBase64 }}" style="width:100%; height:auto;" alt="Approval QR Code">
                            </div>
                        @else
                            <div
                                style="width:100px; height:100px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center;">
                                No QR Code
                            </div>
                        @endif
                        <br>
                        <p><b>{{ $quotation->approval_name ?? '__________________' }}</b></p>
                        <p>
                            Date:
                            {{ $quotation->approved_at ? \Carbon\Carbon::parse($quotation->approved_at)->format('d M Y') : '' }}
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>
