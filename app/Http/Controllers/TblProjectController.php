<?php

namespace App\Http\Controllers;

use App\Models\Kerjaan;
use App\Models\Pak;
use App\Models\ProjectTbl;
use App\Models\User;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class TblProjectController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $projects = ProjectTbl::with(['clients', 'kerjaan', 'creator'])
            ->when((int) Auth::user()->role_id === 2, fn ($query) => $query->accessibleToClient(Auth::id()))
            ->orderBy('created_at', 'desc')
            ->get();
        $listclient = User::where('role_id', 2)->get();
        $listkerjaan = Kerjaan::select('id', 'nama_kerjaan')->get();
        $listUser = User::where('role_id', 1)->get();
        $listPak = Pak::orderBy('date', 'desc')->get();

        return view('projects.index', compact(
            'projects',
            'listclient',
            'listkerjaan',
            'listUser',
            'listPak'
        ));
    }

    public function getListProject(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = ProjectTbl::with([
            'clients',
            'kerjaan',
            'pics',
            'pak',
            'invoices.payments',
        ])
            ->select('projects.*')
            ->orderBy('id', 'desc');

        if (auth()->user()->role_id == 2) {
            $query->accessibleToClient(auth()->id());
        }

        $yearNow = Carbon::now()->year;

        $years = $request->input('tahun');

        // Normalisasi jadi array
        if (is_string($years)) {
            // bisa "2024,2025" atau "2025"
            $years = explode(',', $years);
        } elseif (! is_array($years)) {
            $years = [];
        }

        // Bersihkan: trim, cast int, buang yang tidak valid
        $years = collect($years)
            ->map(fn ($y) => (int) trim($y))
            ->filter(fn ($y) => $y >= 2000 && $y <= ($yearNow + 5)) // batas aman, silakan ubah
            ->unique()
            ->values()
            ->all();

        // Default: tahun sekarang
        if (empty($years)) {
            $years = [$yearNow];
        }

        $query->where(function ($query) use ($years) {
            foreach ($years as $year) {
                $query->orWhereYear('projects.start', $year);
            }
        });

        return DataTables::of($query)
            ->addColumn('project_name', function ($project) {
                return $project->nama_project ?? '-';
            })
            ->addColumn('client', function ($project) {
                if ($project->clients->isEmpty()) {
                    return '-';
                }

                return $project->clients->map(function ($client) {
                    return $client->company
                        ? "{$client->name} ({$client->company})"
                        : $client->name;
                })->implode('; ');
            })
            ->addColumn('total_biaya_project', function ($project) {
                if (auth()->user()->role_id == 1) {
                    return $project->total_biaya_project;
                }

                return null;
            })
            ->addColumn('status', function ($project) {
                $totalInvoice = (float) $project->total_biaya_project;

                $totalPaid = $project->invoices
                    ->flatMap(fn ($inv) => $inv->payments)
                    ->sum('amount_paid');

                $remaining = $totalInvoice - $totalPaid;

                if ($totalInvoice == 0) {
                    return '<span class="badge badge-secondary">Belum Ada Invoice</span>';
                }

                if ($remaining <= 0) {
                    return '<span class="badge badge-success">Lunas</span>';
                }

                return '<span class="badge badge-warning">Sisa: '.number_format($remaining, 0, ',', '.').'</span>';
            })
            ->addColumn('selesai', function ($project) {
                $listProses = DB::table('kerjaan_list_proses')
                    ->where('kerjaan_id', $project->kerjaan_id)
                    ->select('list_proses_id', 'urutan')
                    ->get();

                $totalProses = $listProses->count();

                $prosesSelesai = DB::table('project_details')
                    ->where('project_id', $project->id)
                    ->where(function ($query) use ($listProses) {
                        foreach ($listProses as $proses) {
                            $query->orWhere(function ($q) use ($proses) {
                                $q->where('kerjaan_list_proses_id', $proses->list_proses_id)
                                    ->where('urutan_id', $proses->urutan);
                            });
                        }
                    })
                    ->where('status', 'done')
                    ->count();

                $persen = $totalProses > 0 ? round(($prosesSelesai / $totalProses) * 100) : 0;
                $icon = $persen == 100 ? '<i class="fas fa-check text-success ml-1"></i>' : '';

                return "$persen% $icon";
            })
            ->addColumn('periode', function ($project) {
                if ($project->start && $project->end) {
                    return '
                    <small class="d-block">Mulai: '.$project->start->format('d M Y').'</small>
                    <small class="d-block">Selesai: '.$project->end->format('d M Y').'</small>
                ';
                }

                return '<span class="text-muted">Belum ditentukan</span>';
            })
            ->addColumn('aksi', function ($project) {
                $viewBtn = '
                <a class="btn btn-sm btn-info" href="'.route('projects.show', $project->id).'">
                    <i class="fas fa-eye"></i>
                </a>';

                if (auth()->user()->role_id == 1) {
                    $editBtn = '
                    <button type="button" class="btn btn-sm btn-secondary btn-edit-project"
                        data-toggle="modal" data-target="#EditProjectModal"
                        data-id="'.$project->id.'"
                        data-no="'.$project->no_project.'"
                        data-nama="'.$project->nama_project.'"
                        data-clients="'.$project->clients->pluck('id')->implode(';').'"
                        data-kerjaan="'.$project->kerjaan_id.'"
                        data-deskripsi="'.$project->deskripsi.'"
                        data-biaya="'.$project->total_biaya_project.'"
                        data-start="'.optional($project->start)->format('Y-m-d').'"
                        data-end="'.optional($project->end)->format('Y-m-d').'"
                        data-pics="'.$project->pics->pluck('id')->implode(';').'"
                        data-pak="'.($project->pak_id ?? '').'"
                    >
                        <i class="fas fa-edit"></i>
                    </button>';

                    $deleteBtn = '
                    <button type="button" class="btn btn-sm btn-danger btnDeletProject"
                        data-id="'.$project->id.'">
                        <i class="fas fa-trash"></i>
                    </button>';

                    return $viewBtn.' '.$editBtn.' '.$deleteBtn;
                }

                return $viewBtn;
            })
            ->addColumn('pak_number', function ($project) {
                return $project->pak->pak_number ?? '-';
            })
            ->addColumn('pic', function ($project) {
                if (! $project->pics || $project->pics->isEmpty()) {
                    return '-';
                }

                return $project->pics->pluck('name')->implode(';');
            })
            ->rawColumns(['periode', 'aksi', 'selesai', 'status', 'pic'])
            ->make(true);
    }

    public function generateNoProject()
    {
        $year = date('Y');
        $month = date('m');

        $lastProjectThisYear = DB::table('projects')
            ->where('no_project', 'like', "%-{$year}")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProjectThisYear) {
            $lastNumber = (int) explode('/', $lastProjectThisYear->no_project)[0];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $formattedNumber = ($newNumber < 100)
            ? str_pad($newNumber, 2, '0', STR_PAD_LEFT)
            : (string) $newNumber;

        $noProject = "{$formattedNumber}/GPT/{$month}-{$year}";

        return response()->json([
            'no_project' => $noProject,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'nama_project' => 'required|string|max:100',
                'no_project' => 'required|string|unique:projects',
                'client_ids' => 'required|array|min:1',
                'client_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where('role_id', 2)],
                'kerjaan_id' => 'required|exists:kerjaans,id',
                'pak_id' => 'nullable|exists:paks,id',
                'deskripsi' => 'nullable|string',
                'total_biaya_project' => 'nullable|numeric',
                'start' => 'required|date',
                'end' => 'required|date|after_or_equal:start',
                'pic_id' => 'required|array|min:1',
                'pic_id.*' => 'exists:users,id',
            ]);

            $clientIds = array_values($validated['client_ids']);
            $validated['client_id'] = $clientIds[0];
            $validated['created_by'] = Auth::id();

            unset($validated['client_ids']);

            $project = ProjectTbl::create($validated);
            $project->clients()->sync($clientIds);

            $listProses = DB::table('kerjaan_list_proses')
                ->where('kerjaan_id', $validated['kerjaan_id'])
                ->orderBy('urutan', 'asc')
                ->get();

            $startPlan = Carbon::parse($validated['start']);

            foreach ($listProses as $proses) {
                $currentStartPlan = $startPlan->copy();

                DB::table('project_details')->insert([
                    'project_id' => $project->id,
                    'kerjaan_list_proses_id' => $proses->list_proses_id,
                    'urutan_id' => $proses->urutan,
                    'status' => 'pending',
                    'start_plan' => $currentStartPlan,
                    'end_plan' => $currentStartPlan->copy()->addDays($proses->hari - 1),
                    'start_action' => null,
                    'end_action' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $startPlan = $currentStartPlan->copy()->addDays($proses->hari);
            }

            // Simpan PIC (bisa lebih dari 1)
            $picData = collect($validated['pic_id'])->map(function ($userId) use ($project) {
                return [
                    'project_id' => $project->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                ];
            })->toArray();

            DB::table('project_user')->insert($picData);

            // Simpan log aktivitas dengan old_data = null, new_data = project yang baru dibuat
            $this->logActivity(
                "Menambahkan Project {$project->no_project} - {$project->nama_project}",
                $project->no_project,
                null,
                $project->toArray()
            );

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('projects.tampilan')
                ->with('success', 'Project berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: '.$e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function show(ProjectTbl $project)
    {
        $this->authorizeClientAccess($project);

        $project->loadMissing('clients');

        $kerjaanId = $project->kerjaan_id;
        $projectId = $project->id;

        // Query lama untuk steps
        $q = "SELECT
                a.urutan,
                a.list_proses_id,
                b.nama_proses,
                c.status
            FROM kerjaan_list_proses a
            JOIN list_proses b ON a.list_proses_id = b.id
            LEFT JOIN project_details c
                ON b.id = c.kerjaan_list_proses_id
                AND c.project_id = '$projectId'
                AND c.urutan_id = a.urutan
            WHERE a.kerjaan_id = '$kerjaanId'
            ORDER BY a.urutan ASC";

        $processes = DB::select($q);

        $steps = [];
        $stepStatuses = [];
        $stepProcessIds = [];
        $stepUrutan = [];

        foreach ($processes as $process) {
            $key = $process->list_proses_id.'-'.$process->urutan;
            $steps[$key] = $process->nama_proses;
            $stepStatuses[$key] = $process->status ?? 'pending';
            $stepProcessIds[$key] = $process->list_proses_id;
            $stepUrutan[$key] = $process->urutan;
        }

        $timelineQuery = "SELECT
                    b.nama_proses AS title,
                    c.start_plan,
                    c.end_plan,
                    c.start_action,
                    c.end_action
                FROM kerjaan_list_proses a
                JOIN list_proses b ON a.list_proses_id = b.id
                LEFT JOIN project_details c
                    ON a.list_proses_id = c.kerjaan_list_proses_id
                    AND a.urutan = c.urutan_id
                    AND c.project_id = '$projectId'
                WHERE a.kerjaan_id = '$kerjaanId'
                ORDER BY a.urutan ASC";

        $timelineRows = DB::select($timelineQuery);

        // Format timeline data ke array
        $timelineData = array_map(function ($row) {
            return [
                'title' => $row->title,
                'start_plan' => $row->start_plan ? \Carbon\Carbon::parse($row->start_plan)->toDateString() : null,
                'end_plan' => $row->end_plan ? \Carbon\Carbon::parse($row->end_plan)->toDateString() : null,
                'start_action' => $row->start_action ? \Carbon\Carbon::parse($row->start_action)->toDateString() : null,
                'end_action' => $row->end_action ? \Carbon\Carbon::parse($row->end_action)->toDateString() : null,
            ];
        }, $timelineRows);

        return view('projects.show', compact(
            'project',
            'steps',
            'stepStatuses',
            'stepProcessIds',
            'stepUrutan',
            'timelineData' // Kirim timelineData ke view
        ));
    }

    public function update(Request $request, ProjectTbl $project)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        $validated = $request->validate([
            'nama_project' => 'required|string|max:100',
            'no_project' => 'required|string|unique:projects,no_project,'.$project->id,
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where('role_id', 2)],
            'kerjaan_id' => 'required|exists:kerjaans,id',
            'pak_id' => 'nullable|exists:paks,id',
            'deskripsi' => 'nullable|string',
            'total_biaya_project' => 'nullable|numeric',
            'start_project' => 'nullable|date',
            'end_project' => 'nullable|date|after_or_equal:start_project',
            'pics' => 'required|array|min:1',
            'pics.*' => 'exists:users,id',
        ]);

        $clientIds = array_values($validated['client_ids']);
        $validated['client_id'] = in_array($project->client_id, $clientIds)
            ? $project->client_id : $clientIds[0];
        $validated['start'] = $validated['start_project'] ?? null;
        $validated['end'] = $validated['end_project'] ?? null;

        unset($validated['client_ids'], $validated['start_project'], $validated['end_project']);

        $oldStart = $project->start;
        $oldKerjaanId = $project->kerjaan_id;

        $oldData = $project->toArray();

        DB::transaction(function () use ($project, $validated, $clientIds, $oldStart, $oldKerjaanId, $oldData) {
            // Update project
            $project->update($validated);

            $project->clients()->sync($clientIds);
            $project->pics()->sync($validated['pics']);

            $startChanged = isset($validated['start']) &&
                Carbon::parse($validated['start'])->format('Y-m-d') !==
                Carbon::parse($oldStart)->format('Y-m-d');

            $kerjaanChanged = $validated['kerjaan_id'] != $oldKerjaanId;

            if ($startChanged || $kerjaanChanged) {
                // Ambil list proses sesuai kerjaan baru
                $listProses = DB::table('kerjaan_list_proses')
                    ->where('kerjaan_id', $validated['kerjaan_id'])
                    ->orderBy('urutan', 'asc')
                    ->get();

                // Hitung ulang start_plan dan end_plan
                $startPlan = Carbon::parse($validated['start'] ?? $oldStart);

                // Hapus detail lama
                DB::table('project_details')->where('project_id', $project->id)->delete();

                // Insert ulang detail dengan plan yang disesuaikan
                foreach ($listProses as $proses) {
                    $currentStartPlan = $startPlan->copy();

                    DB::table('project_details')->insert([
                        'project_id' => $project->id,
                        'kerjaan_list_proses_id' => $proses->list_proses_id,
                        'urutan_id' => $proses->urutan,
                        'status' => 'pending',
                        'start_plan' => $currentStartPlan,
                        'end_plan' => $currentStartPlan->copy()->addDays($proses->hari - 1),
                        'start_action' => null,
                        'end_action' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $startPlan = $currentStartPlan->copy()->addDays($proses->hari);
                }
            }

            $this->logActivity(
                "Memperbaharui Project {$project->no_project} - {$project->nama_project}",
                $project->no_project,
                $oldData,
                $project->toArray()
            );
        });

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('projects.tampilan')
            ->with('success', 'Project berhasil diperbarui');
    }

    public function destroy(ProjectTbl $project, Request $request)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        DB::beginTransaction();
        try {

            $oldData = $project->toArray();
            $project->delete();

            $this->logActivity(
                "Menghapus Project {$project->no_project} - {$project->nama_project}",
                $project->no_project,
                $oldData,
                null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus project.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function authorizeClientAccess(ProjectTbl $project): void
    {
        if ((int) Auth::user()->role_id !== 2) {
            return;
        }

        abort_unless(
            $project->clients()->whereKey(Auth::id())->exists(),
            403
        );
    }

    public function uploadFiles(Request $request)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'list_proses_id' => 'required|exists:list_proses,id',
            'fileLabel' => 'nullable|array',
            'fileLabel.*' => [
                Rule::requiredIf(function () use ($request) {
                    return ! empty($request->fileLabel);
                }),
                'string',
            ],
            'fileInput' => 'nullable|array',
            'fileInput.*' => [
                Rule::requiredIf(function () use ($request) {
                    return ! empty($request->fileLabel);
                }),
                'file',
                'mimes:pdf,jpg,png,doc,docx,xls,xlsx',
                'max:102400',
            ],
            'start_action' => 'required|date',
            'end_action' => 'required|date|after_or_equal:start_action',
        ]);

        $listProsesId = $request->input('list_proses_id');
        $urutanId = $request->input('urutan_id');

        DB::beginTransaction();

        try {
            // 1. Ambil atau buat project_detail
            $projectDetail = DB::table('project_details')
                ->where('project_id', $request->project_id)
                ->where('kerjaan_list_proses_id', $listProsesId)
                ->where('urutan_id', $urutanId)
                ->first();

            if (! $projectDetail) {
                $projectDetailId = DB::table('project_details')->insertGetId([
                    'project_id' => $request->project_id,
                    'kerjaan_list_proses_id' => $listProsesId,
                    'urutan_id' => $urutanId,
                    'status' => 'in_progress',
                    'start_action' => $request->start_action ?? null,
                    'end_action' => $request->end_action ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $projectDetailId = $projectDetail->id;
                DB::table('project_details')
                    ->where('id', $projectDetailId)
                    ->update([
                        'start_action' => $request->start_action ?? $projectDetail->start_action,
                        'end_action' => $request->end_action ?? $projectDetail->end_action,
                        'updated_at' => now(),
                    ]);
            }

            // 2. Proses file yang diunggah
            if ($request->hasFile('fileInput')) {
                foreach ($request->file('fileInput') as $index => $file) {
                    $namaFile = $request->fileLabel[$index] ?? 'Unnamed File';

                    // 2a. Ambil atau buat list_proses_file
                    $listProsesFile = DB::table('list_proses_files')
                        ->where('list_proses_id', $listProsesId)
                        ->where('nama_file', $namaFile)
                        ->first();

                    if (! $listProsesFile) {
                        $listProsesFileId = DB::table('list_proses_files')->insertGetId([
                            'list_proses_id' => $listProsesId,
                            'nama_file' => $namaFile,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $listProsesFileId = $listProsesFile->id;
                    }

                    // 2b. Simpan file ke storage
                    $directory = 'uploads/projects/'.$request->project_id;
                    $fileName = time().'_'.$file->getClientOriginalName();
                    $file->move(public_path($directory), $fileName);
                    $publicPath = $directory.'/'.$fileName;

                    // 2c. Simpan data ke project_progress_files
                    DB::table('project_progress_files')->insert([
                        'project_detail_id' => $projectDetailId,
                        'list_proses_file_id' => $listProsesFileId,
                        'file_path' => $publicPath,
                        'keterangan' => $namaFile,
                        'uploaded_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'File berhasil diunggah.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUploadedFiles($projectId, Request $request)
    {
        $this->authorizeClientAccess(ProjectTbl::findOrFail($projectId));
        $listProsesId = $request->input('list_proses_id');
        $urutanId = $request->input('urutan_id'); // Ambil dari request

        $query = DB::table('project_progress_files')
            ->join('project_details', 'project_progress_files.project_detail_id', '=', 'project_details.id')
            ->join('list_proses_files', 'project_progress_files.list_proses_file_id', '=', 'list_proses_files.id')
            ->where('project_details.project_id', $projectId);

        // Filter berdasarkan list_proses_id jika tersedia
        if ($listProsesId) {
            $query->where('project_details.kerjaan_list_proses_id', $listProsesId);
        }

        // Filter berdasarkan urutan_id jika tersedia
        if ($urutanId) {
            $query->where('project_details.urutan_id', $urutanId);
        }

        $files = $query->select([
            'project_progress_files.id',
            'list_proses_files.nama_file as name',
            'project_progress_files.file_path',
            'project_progress_files.keterangan',
            'project_progress_files.created_at',
            'project_progress_files.uploaded_by',
            'project_details.start_action',
            'project_details.end_action',
        ])
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => asset($file->file_path),
                    'description' => $file->keterangan,
                    'uploaded_at' => $file->created_at,
                    'uploaded_by' => $file->uploaded_by,
                    'start_action' => $file->start_action,
                    'end_action' => $file->end_action,
                ];
            });

        return response()->json($files);
    }

    public function deleteFile($id)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        // Ambil data file
        $file = DB::table('project_progress_files')->where('id', $id)->first();

        if (! $file) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        // Hapus file dari sistem file jika ada
        if (File::exists(public_path($file->file_path))) {
            File::delete(public_path($file->file_path));
        }

        // Hapus data dari database
        DB::table('project_progress_files')->where('id', $id)->delete();

        return response()->json(['message' => 'File berhasil dihapus.']);
    }

    public function markStepDone($id, Request $request)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        $project_id = $id;
        $validated = $request->validate([
            'list_proses_id' => 'required',
            'urutan_id' => 'required',
        ]);

        try {

            DB::table('project_details')
                ->where('project_id', $project_id)
                ->where('kerjaan_list_proses_id', $validated['list_proses_id'])
                ->where('urutan_id', $validated['urutan_id'])
                ->update([
                    'status' => 'done',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah menjadi selesai',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status: '.$e->getMessage(),
            ], 500);
        }
    }

    public function unmarkStepDone($id, Request $request)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        $project_id = $id;

        // Validasi input kerjaan_list_proses_id
        $validated = $request->validate([
            'kerjaan_list_proses_id' => 'required|integer',
            'urutan_id' => 'required',
        ]);

        try {
            // Update status jadi in_progress
            DB::table('project_details')
                ->where('project_id', $project_id)
                ->where('kerjaan_list_proses_id', $validated['kerjaan_list_proses_id'])
                ->where('urutan_id', $validated['urutan_id'])
                ->update([
                    'status' => 'in_progress',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil dibatalkan dari selesai',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan status: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getListKomentar(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'list_proses_id' => 'required|integer',
            'urutan_id' => 'required|integer',
        ]);

        $this->authorizeClientAccess(ProjectTbl::findOrFail($request->project_id));

        // 1. Cari project_detail_id
        $projectDetail = DB::table('project_details')
            ->where('project_id', $request->project_id)
            ->where('kerjaan_list_proses_id', $request->list_proses_id)
            ->where('urutan_id', $request->urutan_id)
            ->first();

        if (! $projectDetail) {
            return response()->json([]); // Tidak ada komentar karena belum ada project_detail
        }

        // 2. Ambil komentar berdasarkan project_detail_id
        $komentar = DB::table('project_detail_comments as c')
            ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
            ->select(
                'c.id',
                'c.comment',
                'c.created_at',
                'u.name as user_name',
                'r.name as role_name'
            )
            ->where('c.project_detail_id', $projectDetail->id)
            ->orderBy('c.id', 'desc')
            ->get();

        return response()->json($komentar);
    }

    public function storeKomentar(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'list_proses_id' => 'required|integer',
            'urutan_id' => 'required|integer',
            'comment' => 'required|string|max:1000',
        ]);

        $this->authorizeClientAccess(ProjectTbl::findOrFail($request->project_id));

        $projectId = $request->project_id;
        $listProsesId = $request->list_proses_id;
        $urutanId = $request->urutan_id;

        try {
            DB::beginTransaction();

            // 1. Cek apakah sudah ada project_detail
            $projectDetail = DB::table('project_details')
                ->where('project_id', $projectId)
                ->where('kerjaan_list_proses_id', $listProsesId)
                ->where('urutan_id', $urutanId)
                ->first();

            // 2. Kalau belum ada, insert dulu
            if (! $projectDetail) {
                $projectDetailId = DB::table('project_details')->insertGetId([
                    'project_id' => $projectId,
                    'kerjaan_list_proses_id' => $listProsesId,
                    'urutan_id' => $urutanId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $projectDetailId = $projectDetail->id;
            }

            // 3. Simpan komentar ke project_detail_comments
            $inserted = DB::table('project_detail_comments')->insert([
                'project_detail_id' => $projectDetailId,
                'user_id' => Auth::id(),
                'comment' => $request->comment,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! $inserted) {
                throw new \Exception('Gagal menambahkan komentar.');
            }

            DB::commit();

            return response()->json(['message' => 'Komentar berhasil ditambahkan']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal menambahkan komentar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteKomentar($id)
    {
        $comment = DB::table('project_detail_comments')->where('id', $id)->first();
        abort_unless($comment, 404);
        $projectId = DB::table('project_details')->where('id', $comment->project_detail_id)->value('project_id');
        $this->authorizeClientAccess(ProjectTbl::findOrFail($projectId));
        abort_unless((int) Auth::user()->role_id === 1 || (int) $comment->user_id === (int) Auth::id(), 403);

        try {
            DB::table('project_detail_comments')->where('id', $id)->delete();

            return response()->json(['message' => 'Komentar berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus komentar'], 500);
        }
    }

    public function uploadFileAdministrasi(Request $request)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        $id = $request->input('id');
        $files = $request->file('files');
        $fileNames = $request->input('file_names');
        $isInternalFlags = $request->input('is_internal');

        foreach ($files as $index => $file) {
            $name = $fileNames[$index] ?? $file->getClientOriginalName();
            $path = $file->store('administrasi_files', 'public');
            $isInternal = $isInternalFlags[$index] ?? 0;

            DB::table('administrasi_files')->insert([
                'project_id' => $id,
                'file_name' => $name,
                'file_path' => $path,
                'is_internal' => $isInternal,
                'uploaded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'File berhasil diupload']);
    }

    public function getDataAdministrasi($id)
    {
        $this->authorizeClientAccess(ProjectTbl::findOrFail($id));

        $files = DB::table('administrasi_files')
            ->where('project_id', $id)
            ->when((int) Auth::user()->role_id !== 1, fn ($query) => $query->where('is_internal', false))
            ->orderBy('uploaded_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    public function deleteAdministrasiFile($id)
    {
        abort_unless((int) Auth::user()->role_id === 1, 403);
        $file = DB::table('administrasi_files')->where('id', $id)->first();

        if (! $file) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
        }

        // Hapus file dari storage
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        // Hapus dari database
        DB::table('administrasi_files')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'File berhasil dihapus.']);
    }
}
