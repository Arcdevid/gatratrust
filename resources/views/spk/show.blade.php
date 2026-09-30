@extends('layout.app')

@section('title', 'Detail SPK')

@section('content')
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3>Detail SPK</h3>
            <div>
                <a href="{{ route('spk.exportPdf', $spk) }}" target="_blank" class="btn btn-secondary">Export PDF</a>
                <a href="{{ route('spk.edit', $spk) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('spk.index') }}" class="btn btn-light">Kembali</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Nomor</th>
                        <td>{{ $spk->nomor }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ optional($spk->tanggal)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <th>Nama Project</th>
                        <td>{{ $spk->project?->nama_project ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nomor Project</th>
                        <td>{{ $spk->project?->no_project ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pekerjaan</th>
                        <td>{{ $spk->project?->kerjaan?->nama_kerjaan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Client</th>
                        <td>{{ $spk->project?->client?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>{{ $spk->project?->deskripsi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Data Proyek</th>
                        <td>
                            @if (empty($spk->data_proyek))
                                -
                            @else
                                <ul class="mb-0 pl-3">
                                    @foreach ($spk->data_proyek as $item)
                                        <li>{{ $dataProyekOptions[$item] ?? $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
