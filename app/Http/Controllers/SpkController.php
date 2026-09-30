<?php

namespace App\Http\Controllers;

use App\Models\ProjectTbl;
use App\Models\Spk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SpkController extends Controller
{
    public function index(): View
    {
        return view('spk.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = Spk::query()->with('project.kerjaan');

        return DataTables::eloquent($query)
            ->filter(function ($builder) use ($request) {
                $search = $request->input('search.value');

                if (! $search) {
                    return;
                }

                $builder->where(function ($inner) use ($search) {
                    $inner->where('nomor', 'like', "%{$search}%")
                        ->orWhereHas('project', function ($projectQuery) use ($search) {
                            $projectQuery->where('nama_project', 'like', "%{$search}%")
                                ->orWhere('no_project', 'like', "%{$search}%")
                                ->orWhereHas('kerjaan', function ($kerjaanQuery) use ($search) {
                                    $kerjaanQuery->where('nama_kerjaan', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->addColumn('project', function (Spk $spk) {
                $projectName = $spk->project?->nama_project ?? '-';
                $kerjaanName = $spk->project?->kerjaan?->nama_kerjaan;

                if (! $kerjaanName) {
                    return $projectName;
                }

                return $projectName.' / '.$kerjaanName;
            })
            ->addColumn('data_proyek_badges', function (Spk $spk) {
                if (empty($spk->data_proyek)) {
                    return '<span class="text-muted">-</span>';
                }

                return collect($spk->data_proyek)
                    ->map(function (string $item) {
                        return Spk::DATA_PROYEK_OPTIONS[$item] ?? $item;
                    })
                    ->implode(', ');
            })
            ->addColumn('action', function (Spk $spk) {
                $showUrl = route('spk.show', $spk);
                $editUrl = route('spk.edit', $spk);
                $deleteUrl = route('spk.destroy', $spk);
                $pdfUrl = route('spk.exportPdf', $spk);

                return "
                    <a href=\"{$showUrl}\" class=\"btn btn-sm btn-info\" title=\"Detail\"><i class=\"fas fa-eye\"></i></a>
                    <a href=\"{$editUrl}\" class=\"btn btn-sm btn-primary\" title=\"Edit\"><i class=\"fas fa-edit\"></i></a>
                    <button type=\"button\" class=\"btn btn-sm btn-danger btn-delete-spk\" data-url=\"{$deleteUrl}\" data-nomor=\"{$spk->nomor}\" title=\"Delete\"><i class=\"fas fa-trash\"></i></button>
                    <a href=\"{$pdfUrl}\" class=\"btn btn-sm btn-secondary\" target=\"_blank\" title=\"Export PDF\"><i class=\"fas fa-file-pdf\"></i></a>
                ";
            })
            ->editColumn('tanggal', function (Spk $spk) {
                return optional($spk->tanggal)->format('d-m-Y');
            })
            ->rawColumns(['action', 'data_proyek_badges'])
            ->toJson();
    }

    public function create(): View
    {
        $projects = ProjectTbl::query()->with('kerjaan')->orderBy('no_project')->get();

        return view('spk.create', [
            'projects' => $projects,
            'dataProyekOptions' => Spk::DATA_PROYEK_OPTIONS,
            'newSpkNo' => $this->generateSpkNumber(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        Spk::create($validated);

        return redirect()->route('spk.index')->with('success', 'SPK berhasil dibuat.');
    }

    public function show(Spk $spk): View
    {
        $spk->load('project.kerjaan', 'project.client');

        return view('spk.show', [
            'spk' => $spk,
            'dataProyekOptions' => Spk::DATA_PROYEK_OPTIONS,
        ]);
    }

    public function edit(Spk $spk): View
    {
        $projects = ProjectTbl::query()->with('kerjaan')->orderBy('no_project')->get();

        return view('spk.edit', [
            'spk' => $spk,
            'projects' => $projects,
            'dataProyekOptions' => Spk::DATA_PROYEK_OPTIONS,
        ]);
    }

    public function update(Request $request, Spk $spk): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $spk->update($validated);

        return redirect()->route('spk.index')->with('success', 'SPK berhasil diperbarui.');
    }

    public function destroy(Spk $spk): RedirectResponse
    {
        $spk->delete();

        return redirect()->route('spk.index')->with('success', 'SPK berhasil dihapus.');
    }

    public function exportPdf(Spk $spk)
    {
        $spk->load('project.kerjaan', 'project.client', 'project.pak', 'project.pics', 'project.pak.karyawans', 'project.pak.karyawans.jabatan');

        // dd($spk->project->pak->karyawans);

        $pdf = Pdf::loadView('spk.pdf', [
            'spk' => $spk,
            'dataProyekOptions' => Spk::DATA_PROYEK_OPTIONS,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('spk-'.$spk->id.'.pdf');
    }

    private function generateSpkNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $monthYear = "{$month}-{$year}";

        $lastSpkThisYear = Spk::query()
            ->where('nomor', 'like', "%/GPT-SPK/%-{$year}")
            ->orderByDesc('id')
            ->first();

        if (! $lastSpkThisYear) {
            return '001/GPT-SPK/'.$monthYear;
        }

        $lastNumber = (int) Str::before($lastSpkThisYear->nomor, '/');
        $newNumber = str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);

        return $newNumber.'/GPT-SPK/'.$monthYear;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'nomor' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'project_id' => ['required', 'exists:projects,id'],
            'data_proyek' => ['nullable', 'array'],
            'data_proyek.*' => ['in:'.implode(',', array_keys(Spk::DATA_PROYEK_OPTIONS))],
        ]);
    }
}
