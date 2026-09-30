@extends('layout.app')

@section('title', 'Edit SPK')

@section('content')
    <div class="container-fluid">
        <div class="page-header">
            <h3>Edit SPK</h3>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('spk.update', $spk) }}" method="POST">
                    @method('PUT')
                    @include('spk._form')
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            $('.js-project-select').select2({
                placeholder: '-- Pilih Project --',
                width: '100%',
                allowClear: true
            });
        });
    </script>
@endsection
