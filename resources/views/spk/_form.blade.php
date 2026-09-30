@csrf

<div class="form-group">
    <label for="nomor">Nomor SPK</label>
    <input type="text" name="nomor" id="nomor" class="form-control"
        value="{{ old('nomor', $spk->nomor ?? ($newSpkNo ?? '')) }}" readonly required>
</div>

<div class="form-group">
    <label for="tanggal">Tanggal</label>
    <input type="date" name="tanggal" id="tanggal" class="form-control"
        value="{{ old('tanggal', isset($spk) && $spk->tanggal ? $spk->tanggal->format('Y-m-d') : '') }}" required>
</div>

<div class="form-group">
    <label for="project_id">Project</label>
    <select name="project_id" id="project_id" class="form-control js-project-select" required>
        <option value="">-- Pilih Project --</option>
        @foreach ($projects as $project)
            <option value="{{ $project->id }}" @selected(old('project_id', $spk->project_id ?? '') == $project->id)>
                {{ $project->no_project }} - {{ $project->nama_project }}
                @if ($project->kerjaan)
                    ({{ $project->kerjaan->nama_kerjaan }})
                @endif
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Data Proyek</label>
    @php
        $selectedDataProyek = old('data_proyek', $spk->data_proyek ?? []);
    @endphp

    @foreach ($dataProyekOptions as $value => $label)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="data_proyek[]" id="{{ $value }}"
                value="{{ $value }}" @checked(in_array($value, $selectedDataProyek, true))>
            <label class="form-check-label" for="{{ $value }}">{{ $label }}</label>
        </div>
    @endforeach
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('spk.index') }}" class="btn btn-light">Kembali</a>
</div>
