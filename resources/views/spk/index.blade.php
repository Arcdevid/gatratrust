@extends('layout.app')

@section('title', 'SPK')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <h3 class="text-primary font-weight-bold">SPK</h3>
                </div>
                <div class="col-lg-4 text-right">
                    <a href="{{ route('spk.create') }}" class="btn btn-primary">Tambah SPK</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="container">
                        <table class="table table-bordered" id="spkTable">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Tanggal</th>
                                    <th>Project</th>
                                    <th>Data Proyek</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteSpkForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('script')
    <script>
        $(function() {
            $('#spkTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('spk.datatable') }}',
                columns: [{
                        data: 'nomor',
                        name: 'nomor'
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'project',
                        name: 'project.nama_project'
                    },
                    {
                        data: 'data_proyek_badges',
                        name: 'data_proyek',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.btn-delete-spk', function() {
                const deleteUrl = $(this).data('url');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Hapus SPK?',
                    text: 'Data SPK ' + nomor + ' akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('#deleteSpkForm');
                        form.attr('action', deleteUrl);
                        form.trigger('submit');
                    }
                });
            });
        });
    </script>
@endsection
