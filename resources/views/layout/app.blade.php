<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('template/img/Logo_gatra.png') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('template//favicon.ico') }}">

    <link src="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('template/plugins/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/ionicons/dist/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/icon-kit/dist/css/iconkit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/weather-icons/css/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }} ">
    <link rel="stylesheet" href="{{ asset('template/dist/css/theme.min.css') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="{{ asset('template/src/js/vendor/modernizr-2.8.3.min.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @yield('css')
    <style>
        .logo-mini {
            display: none;
            vertical-align: middle;
        }

        .logo-full {
            display: inline-block;
        }

        .app-sidebar.collapsed .logo-full {
            display: none !important;
        }

        .app-sidebar.collapsed .logo-mini {
            display: inline-block !important;
        }

        .select2-selection__choice {
            padding-left: 1.6em !important;
            position: relative;
            display: inline-flex;
            align-items: center;
            height: 1.6em;
            line-height: 1.6em;
        }
    </style>

</head>

<body>

    <div class="wrapper">
        @include('layout.main')
    </div>

    <script src="{{ asset('template/src/js/vendor/modernizr-2.8.3.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        window.jQuery || document.write(
            '<script src="{{ asset('template/src/js/vendor/jquery-3.3.1.min.js') }}"><\/script>');
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('template/plugins/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('template/plugins/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('template/plugins/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('template/plugins/screenfull/dist/screenfull.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="{{ asset('template/dist/js/theme.min.js') }}"></script>

    @yield('script')

    <script>
        $(function() {
            const flashSuccess = @json(session('success'));
            const flashError = @json(session('error'));
            const flashWarning = @json(session('warning'));
            const validationErrors = @json($errors->all());

            if (flashSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: flashSuccess,
                });
            }

            if (flashError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: flashError,
                });
            }

            if (flashWarning) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: flashWarning,
                });
            }

            if (validationErrors && validationErrors.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '<ul style="text-align:left; margin:0; padding-left:18px;">' +
                        validationErrors.map(error => '<li>' + error + '</li>').join('') +
                        '</ul>',
                });
            }
        });
    </script>


    <!-- Google Analytics -->
    <script>
        (function(b, o, i, l, e, r) {
            b.GoogleAnalyticsObject = l;
            b[l] || (b[l] = function() {
                (b[l].q = b[l].q || []).push(arguments)
            });
            b[l].l = +new Date;
            e = o.createElement(i);
            r = o.getElementsByTagName(i)[0];
            e.src = 'https://www.google-analytics.com/analytics.js';
            r.parentNode.insertBefore(e, r)
        }(window, document, 'script', 'ga'));
        ga('create', 'UA-XXXXX-X', 'auto');
        ga('send', 'pageview');
    </script>
</body>
