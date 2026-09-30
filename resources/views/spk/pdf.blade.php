<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>SPK {{ $spk->nomor }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        @page {
            margin-top: 110px;
            margin-bottom: 80px;
            margin-left: 20px;
            margin-right: 20px;
        }

        .header-page {
            position: fixed;
            top: -110px;
            left: 0;
            right: 0;
            height: 110px;
            background: white;
            z-index: 100;
        }

        .header {
            width: 100%;
            /* border-bottom: 2px solid #080808; */
            /* padding-bottom: 5px; */
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

        .footer {
            position: fixed;
            bottom: -100px;
            left: 0;
            right: 0;
            height: 80px;
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

        .no-border,
        .no-border td,
        .no-border th {
            border: none !important;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 4px 0;
        }

        .section-title {
            font-weight: bold;
            margin: 10px 0 8px 0;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 11px;
        }

        .label-col {
            width: 30%;
            font-weight: bold;
            background: #f5f5f5;
        }

        .check-table td {
            width: 50%;
        }

        .check {
            font-weight: bold;
            margin-right: 6px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header-page">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width:130px;">
                        @php
                            $path = public_path('template/img/LOGO_Gatra1.png');
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $base64 = file_exists($path)
                                ? 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path))
                                : '';
                        @endphp
                        <img src="{{ $base64 }}" alt="logo" class="logo" />
                    </td>
                    <td>
                        <div class="header-text">
                            <h1>PT. GATRA PERDANA TRUSTRUE</h1>
                            <p>Calibration Test, Consultant, General Supplier, &amp; Digital Agency for your Business
                            </p>
                            <p>Kawasan Komplek Ruko Golden BCI Blok T3 No. 12 Bengkong Laut, Kec. Bengkong, Kota Batam
                            </p>
                        </div>
                    </td>
                </tr>
            </table>

            <hr style="border: 1px solid #0C6401; margin: 4px 0 6px 0;">

        </div>
    </div>

    <div class="title">SURAT PERINTAH KERJA</div>
    <div class="title">METAL LOGAM & TEST LABORATORY</div>

    <div class="footer">
        <img src="{{ public_path('template/img/LOGO_Gatra1.png') }}" alt="Logo">
        PT. GATRA PERDANA TRUSTRUE | www.gatraperdanatrustrue.com | Page <span class="pagenum"></span>
    </div>

    <div class="content">
        <table class="no-border">
            <tr>
                <td style="width:50%; vertical-align: top;">
                    <table class="no-border">
                        <tr>
                            <td style="width: 30%; font-weight: bold;">Nomor SPK</td>
                            <td>: {{ $spk->nomor }}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-weight: bold;">Tanggal</td>
                            <td>: {{ optional($spk->tanggal)->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-weight: bold;">Lokasi</td>
                            <td>:
                                @php
                                    $lokasi = $spk->project?->pak?->location;
                                @endphp

                                {{ match ($lokasi) {
                                    'luar_kota' => 'Luar Kota',
                                    'dalam_kota' => 'Dalam Kota',
                                    default => '-',
                                } }}
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="width:50%; vertical-align: top;">
                    <table class="no-border">
                        <tr>
                            <td style="width: 30%; font-weight: bold;">Tanggal Mulai</td>
                            <td>: {{ optional($spk->project?->start)->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; font-weight: bold;">Tanggal Selesai</td>
                            <td>: {{ optional($spk->project?->end)->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>


        <div class="section-title">I. PENUGASAN</div>
        <table>
            <tr>
                <td class="label-col">Nama</td>
                <td>{{ $spk->project?->pak?->karyawans->pluck('nama_lengkap')->implode(', ') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Jabatan</td>
                <td>{{ $spk->project?->pak?->karyawans->pluck('jabatan.nama_jabatan')->implode(', ') ?? '-' }}</td>
            </tr>
            {{-- <tr>
                <td class="label-col">Lokasi</td>
                <td>{{ $spk->project?->pak?->location ?? '-' }}</td>
            </tr> --}}
        </table>




        <div class="section-title">II. DATA CLIENT</div>
        <table>
            <tr>
                <td class="label-col">Nama Project</td>
                <td>{{ $spk->project?->nama_project ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">No. Project</td>
                <td>{{ $spk->project?->no_project ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Pekerjaan</td>
                <td>{{ $spk->project?->kerjaan?->nama_kerjaan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Client</td>
                <td>{{ $spk->project?->client?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Deskripsi</td>
                <td>{{ $spk->project?->deskripsi ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">III. DATA PROYEK</div>
        <table class="check-table">
            @php
                $selected = $spk->data_proyek ?? [];
                $chunks = array_chunk($dataProyekOptions, 2, true);
            @endphp
            @foreach ($chunks as $chunk)
                <tr>
                    @foreach ($chunk as $key => $label)
                        <td>
                            <span class="check">{{ in_array($key, $selected, true) ? '✓' : '□' }}</span>
                            {{ $label }}
                        </td>
                    @endforeach
                    @if (count($chunk) === 1)
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </table>

        <div class="section-title">IV. KETENTUAN UMUM</div>




        <div class="section-title">V. PENGESAHAN</div>

        <table class="no-border" style="margin-top:40px;">
            <tr>
                <td style="width:60%; border:none;"></td>

                <td style="width:40%; text-align:center; border:none;">
                    <span style="font-weight: bold;">Supervisor Consultant</span>
                    <br><br><br><br><br><br>
                    <p>__________________</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
