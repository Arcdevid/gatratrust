<header class="header-top" header-theme="light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <div class="top-menu d-flex align-items-center">
                <button type="button" class="btn-icon mobile-nav-toggle d-lg-none"><span></span></button>
                {{-- <div class="header-search">
                    <div class="input-group">
                        <span class="input-group-addon search-close"><i class="ik ik-x"></i></span>
                        <input type="text" class="form-control">
                        <span class="input-group-addon search-btn"><i class="ik ik-search"></i></span>
                    </div>
                </div> --}}
                <button type="button" id="navbar-fullscreen" class="nav-link"><i class="ik ik-maximize"></i></button>
            </div>
            <div class="top-menu d-flex align-items-center">
                {{-- <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notiDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><i class="ik ik-bell"></i><span
                            class="badge bg-danger">3</span></a>
                    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notiDropdown">
                        <h4 class="header">Notifications</h4>
                        <div class="notifications-wrap">
                            <a href="#" class="media">
                                <span class="d-flex">
                                    <i class="ik ik-check"></i>
                                </span>
                                <span class="media-body">
                                    <span class="heading-font-family media-heading">Invitation accepted</span>
                                    <span class="media-content">Your have been Invited ...</span>
                                </span>
                            </a>
                            <a href="#" class="media">
                                <span class="d-flex">
                                    <img src="img/users/1.jpg" class="rounded-circle" alt="">
                                </span>
                                <span class="media-body">
                                    <span class="heading-font-family media-heading">Steve Smith</span>
                                    <span class="media-content">I slowly updated projects</span>
                                </span>
                            </a>
                            <a href="#" class="media">
                                <span class="d-flex">
                                    <i class="ik ik-calendar"></i>
                                </span>
                                <span class="media-body">
                                    <span class="heading-font-family media-heading">To Do</span>
                                    <span class="media-content">Meeting with Nathan on Friday 8 AM ...</span>
                                </span>
                            </a>
                        </div>
                        <div class="footer"><a href="javascript:void(0);">See all activity</a></div>
                    </div>
                </div> --}}
                {{-- <button type="button" class="nav-link ml-10 right-sidebar-toggle"><i
                        class="ik ik-message-square"></i><span class="badge bg-success">3</span></button>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><i class="ik ik-plus"></i></a>
                    <div class="dropdown-menu dropdown-menu-right menu-grid" aria-labelledby="menuDropdown">
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top"
                            title="Dashboard"><i class="ik ik-bar-chart-2"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Message"><i
                                class="ik ik-mail"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Accounts"><i
                                class="ik ik-users"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Sales"><i
                                class="ik ik-shopping-cart"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Purchase"><i
                                class="ik ik-briefcase"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Pages"><i
                                class="ik ik-clipboard"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Chats"><i
                                class="ik ik-message-square"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Contacts"><i
                                class="ik ik-map-pin"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Blocks"><i
                                class="ik ik-inbox"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Events"><i
                                class="ik ik-calendar"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top"
                            title="Notifications"><i class="ik ik-bell"></i></a>
                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="More"><i
                                class="ik ik-more-horizontal"></i></a>
                    </div>
                </div> --}}
                {{-- <button type="button" class="nav-link ml-10" id="apps_modal_btn" data-toggle="modal"
                    data-target="#appsModal"><i class="ik ik-grid"></i></button> --}}

                <div class="dropdown d-flex align-items-center">
                    <div class="text-right mr-2">
                        <span class="font-weight-bold">
                            @if (!empty(Auth::user()->company))
                                {{ Auth::user()->company }}
                            @else
                                {{ Auth::user()->name }}
                            @endif
                        </span>
                    </div>
                    <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <img class="avatar" src="{{ asset('template/img/user_main.jpg') }}" alt="">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                            <i class="ik ik-user dropdown-icon"></i> Profile
                        </a>
                        {{-- <a class="dropdown-item" href="#"><i class="ik ik-settings dropdown-icon"></i>
                            Settings</a>
                        <a class="dropdown-item" href="#"><span class="float-right"><span
                                    class="badge badge-primary">6</span></span><i class="ik ik-mail dropdown-icon"></i>
                            Inbox</a>
                        <a class="dropdown-item" href="#"><i class="ik ik-navigation dropdown-icon"></i>
                            Message</a> --}}
                        <a href="#" onclick="confirmLogout()" class="dropdown-item">
                            <i class="ik ik-power dropdown-icon"></i> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</header>

<div class="page-wrap">
    <div class="app-sidebar colored collapsed">
        <div class="sidebar-header">
            <a class="header-brand" href="#">
                <span class="text logo-full">
                    Gatratrust
                    <img src="{{ asset('template/img/Logo_gatra.png') }}" alt="Logo"
                        style="width: 50px; height: 50px; margin-left: 10px;">
                </span>
                <span class="logo-mini" style="display:none;">
                    <img src="{{ asset('template/img/Logo_gatra.png') }}" alt="Logo"
                        style="width: 50px; height: 50px;">
                    <span class="text">Gatratrust</span>
                </span>
            </a>
            <button type="button" class="nav-toggle"><i data-toggle="expanded"
                    class="ik ik-toggle-right toggle-icon"></i></button>
            <button id="sidebarClose" class="nav-close"><i class="ik ik-x"></i></button>
        </div>

        <div class="sidebar-content">
            <div class="nav-container">
                <nav id="main-menu-navigation" class="navigation-main">
                    <div class="nav-lavel">Gatra Consultant</div>

                    @if (in_array(Auth::user()->role_id, [1, 3, 4]))
                        <div class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}">
                                <i class="ik ik-bar-chart-2"></i><span>Dashboard</span>
                            </a>
                        </div>
                    @endif

                    @if (in_array(Auth::user()->role_id, [1, 3, 4]))
                        <div class="nav-item has-sub {{ request()->routeIs('quotations.index') || request()->routeIs('pak.*') || request()->routeIs('spk.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="fas fa-retweet"></i><span>Simulasi</span>
                            </a>
                            <div class="submenu-content">
                                <a href="{{ route('quotations.index') }}"
                                    class="menu-item {{ request()->routeIs('quotations.index') ? 'active' : '' }}">Quotation</a>
                                <a href="{{ route('pak.index') }}"
                                    class="menu-item {{ request()->routeIs('pak.*') ? 'active' : '' }}">PAK</a>
                                <a href="{{ route('spk.index') }}"
                                    class="menu-item {{ request()->routeIs('spk.*') ? 'active' : '' }}">SPK</a>

                            </div>

                        </div>
                    @endif
                    {{-- Project --}}
                    <div class="nav-item {{ request()->routeIs('projects.tampilan') ? 'active' : '' }}">
                        <a href="{{ route('projects.tampilan') }}">
                            <i class="ik ik-folder"></i><span>Project</span>
                        </a>
                    </div>


                    {{-- <div class="nav-item {{ request()->routeIs('spk.*') ? 'active' : '' }}">
                        <a href="{{ route('spk.index') }}">
                            <i class="ik ik-file"></i><span>SPK</span>
                        </a>
                    </div> --}}

                    @if (in_array(Auth::user()->role_id, [1, 3, 4]))
                        {{-- Invoice --}}
                        <div class="nav-item {{ request()->routeIs('invoice.index') ? 'active' : '' }}">
                            <a href="{{ route('invoice.index') }}">
                                <i class="ik ik-file-text"></i><span>Invoice</span>
                            </a>
                        </div>

                        {{-- Kwitansi --}}
                        <div class="nav-item {{ request()->routeIs('kwitansi.index') ? 'active' : '' }}">
                            <a href="{{ route('kwitansi.index') }}">
                                <i class="fas fa-file-invoice"></i><span>Kwitansi</span>
                            </a>
                        </div>

                        {{-- FPU --}}
                        <div
                            class="nav-item {{ request()->routeIs('fpus.index') || request()->routeIs('fpus.create') ? 'active' : '' }}">
                            <a href="{{ route('fpus.index') }}">
                                <i class="fas fa-hand-holding-usd"></i><span>FPU</span>
                            </a>
                        </div>
                        <div
                            class="nav-item has-sub {{ request()->routeIs('vendor.index') || request()->routeIs('listproses.index') || request()->routeIs('kerjaan.show') ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="ik ik-truck"></i><span>Vendor</span>
                            </a>
                            <div class="submenu-content">
                                <a href="{{ route('vendor.index') }}"
                                    class="menu-item {{ request()->routeIs('vendor.index') ? 'active' : '' }}">Daftar
                                    vendor</a>
                                <a href="" class="menu-item">Invoice</a>
                                <a href="" class="menu-item">Kwitansi</a>

                            </div>
                        </div>

                        {{-- Master Data --}}
                        <div
                            class="nav-item has-sub {{ request()->routeIs('user.tampilan') || request()->routeIs('listproses.index') || request()->routeIs('kerjaan.show') ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="ik ik-layers"></i><span>Master Data</span>
                            </a>
                            <div class="submenu-content">
                                <a href="{{ route('user.tampilan') }}"
                                    class="menu-item {{ request()->routeIs('user.tampilan') ? 'active' : '' }}">User</a>
                                <a href="{{ route('activity-logs.index') }}"
                                    class="menu-item {{ request()->routeIs('activity-logs.index') ? 'active' : '' }}">Aktifitas
                                    Log</a>
                                <a href="{{ route('listproses.index') }}"
                                    class="menu-item {{ request()->routeIs('listproses.index') ? 'active' : '' }}">List
                                    Proses</a>
                                <a href="{{ route('kerjaan.show') }}"
                                    class="menu-item {{ request()->routeIs('kerjaan.show') ? 'active' : '' }}">Tipe
                                    Project</a>
                            </div>
                        </div>

                        <div class="nav-lavel">Data Company</div>

                        {{-- Daily Activity --}}
                        <div class="nav-item {{ request()->routeIs('daily.index') ? 'active' : '' }}">
                            <a href="{{ route('daily.index') }}">
                                <i class="fas fa-user-tag"></i><span>Daily Activity</span>
                            </a>
                        </div>

                        {{-- Accounting --}}
                        {{-- <div class="nav-item {{ request()->routeIs('accounting.index') ? 'active' : '' }}">
                            <a href="{{ route('accounting.index') }}">
                                <i class="fas fa-coins"></i><span>Accounting</span>
                            </a>
                        </div> --}}

                        <div
                            class="nav-item has-sub {{ request()->routeIs('coa.index') || request()->routeIs('accounting.index') || request()->routeIs('accounting-settings.index') || request()->routeIs('journals.index') ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="fas fa-coins"></i><span>Accounting</span>
                            </a>
                            <div class="submenu-content">
                                <a href="{{ route('coa.index') }}"
                                    class="menu-item {{ request()->routeIs('coa.index') ? 'active' : '' }}">COA</a>
                                <a href="{{ route('accounting-settings.index') }}"
                                    class="menu-item {{ request()->routeIs('accounting-settings.index') ? 'active' : '' }}">Accounting
                                    Settings</a>
                                <a href="{{ route('journals.index') }}"
                                    class="menu-item {{ request()->routeIs('journals.index') ? 'active' : '' }}">Jurnal</a>
                                <a href="{{ route('accounting.index') }}"
                                    class="menu-item {{ request()->routeIs('accounting.index') ? 'active' : '' }}">Petty
                                    Cash</a>

                            </div>
                        </div>

                        <div class="nav-item has-sub {{ request()->routeIs('laporan.*') ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="fas fa-file-signature"></i><span>Laporan</span>
                            </a>
                            <div class="submenu-content">
                                <a href="{{ route('laporan.buku-besar.index') }}"
                                    class="menu-item {{ request()->routeIs('laporan.buku-besar.index') ? 'active' : '' }}">Buku
                                    Besar</a>
                                <a href="{{ route('laporan.neraca.index') }}"
                                    class="menu-item {{ request()->routeIs('laporan.neraca.index') ? 'active' : '' }}">Neraca</a>
                                <a href="{{ route('laporan.laba-rugi.index') }}"
                                    class="menu-item {{ request()->routeIs('laporan.laba-rugi.index') ? 'active' : '' }}">Laba
                                    Rugi</a>
                            </div>
                        </div>

                        {{-- Data Karyawan --}}
                        <div class="nav-item {{ request()->routeIs('karyawan.index') ? 'active' : '' }}">
                            <a href="{{ route('karyawan.index') }}">
                                <i class="fas fa-users"></i><span>Data Karyawan</span>
                            </a>
                        </div>
                        <div class="nav-item {{ request()->routeIs('jabatan.index') ? 'active' : '' }}">
                            <a href="{{ route('jabatan.index') }}">
                                <i class="fas fa-network-wired"></i><span>Jabatan</span>
                            </a>
                        </div>
                        <div class="nav-item {{ request()->routeIs('assets.index') ? 'active' : '' }}">
                            <a href="{{ route('assets.index') }}">
                                <i class="fas fa-archive"></i><span>Asset</span>
                            </a>
                        </div>
                        <div
                            class="nav-item has-sub {{ request()->routeIs('procedures.index') ? 'active open' : '' }}">
                            <a href="javascript:void(0)">
                                <i class="far fa-folder-open"></i><span>Dokumen ISO</span>
                            </a>
                            <div class="submenu-content">
                                <a href="{{ route('procedures.index') }}"
                                    class="menu-item {{ request()->routeIs('procedures.index') ? 'active' : '' }}">Prosedur</a>
                                <a href="" class="menu-item">Instruksi Kerja</a>
                            </div>
                        </div>
                    @endif
                </nav>

            </div>
        </div>
    </div>
    <div class="main-content">
        @yield('content')
    </div>

    <aside class="right-sidebar">
        <div class="sidebar-chat" data-plugin="chat-sidebar">
            <div class="sidebar-chat-info">
                <h6>Chat List</h6>
                <form class="mr-t-10">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Search for friends ...">
                        <i class="ik ik-search"></i>
                    </div>
                </form>
            </div>
            <div class="chat-list">
                <div class="list-group row">
                    <a href="javascript:void(0)" class="list-group-item" data-chat-user="Gene Newman">
                        <figure class="user--online">
                            <img src="img/users/1.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Gene Newman</span> <span
                                class="username">@gene_newman</span>
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item" data-chat-user="Billy Black">
                        <figure class="user--online">
                            <img src="img/users/2.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Billy Black</span> <span
                                class="username">@billyblack</span>
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item" data-chat-user="Herbert Diaz">
                        <figure class="user--online">
                            <img src="img/users/3.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Herbert Diaz</span> <span class="username">@herbert</span>
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item" data-chat-user="Sylvia Harvey">
                        <figure class="user--busy">
                            <img src="img/users/4.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Sylvia Harvey</span> <span class="username">@sylvia</span>
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item active" data-chat-user="Marsha Hoffman">
                        <figure class="user--busy">
                            <img src="img/users/5.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Marsha Hoffman</span> <span
                                class="username">@m_hoffman</span>
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item" data-chat-user="Mason Grant">
                        <figure class="user--offline">
                            <img src="img/users/1.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Mason Grant</span> <span
                                class="username">@masongrant</span>
                        </span>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item" data-chat-user="Shelly Sullivan">
                        <figure class="user--offline">
                            <img src="img/users/2.jpg" class="rounded-circle" alt="">
                        </figure><span><span class="name">Shelly Sullivan</span> <span
                                class="username">@shelly</span></span>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <div class="chat-panel" hidden>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <a href="javascript:void(0);"><i class="ik ik-message-square text-success"></i></a>
                <span class="user-name">John Doe</span>
                <button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="card-body">
                <div class="widget-chat-activity flex-1">
                    <div class="messages">
                        <div class="message media reply">
                            <figure class="user--online">
                                <a href="#">
                                    <img src="img/users/3.jpg" class="rounded-circle" alt="">
                                </a>
                            </figure>
                            <div class="message-body media-body">
                                <p>Epic Cheeseburgers come in all kind of styles.</p>
                            </div>
                        </div>
                        <div class="message media">
                            <figure class="user--online">
                                <a href="#">
                                    <img src="img/users/1.jpg" class="rounded-circle" alt="">
                                </a>
                            </figure>
                            <div class="message-body media-body">
                                <p>Cheeseburgers make your knees weak.</p>
                            </div>
                        </div>
                        <div class="message media reply">
                            <figure class="user--offline">
                                <a href="#">
                                    <img src="img/users/5.jpg" class="rounded-circle" alt="">
                                </a>
                            </figure>
                            <div class="message-body media-body">
                                <p>Cheeseburgers will never let you down.</p>
                                <p>They'll also never run around or desert you.</p>
                            </div>
                        </div>
                        <div class="message media">
                            <figure class="user--online">
                                <a href="#">
                                    <img src="img/users/1.jpg" class="rounded-circle" alt="">
                                </a>
                            </figure>
                            <div class="message-body media-body">
                                <p>A great cheeseburger is a gastronomical event.</p>
                            </div>
                        </div>
                        <div class="message media reply">
                            <figure class="user--busy">
                                <a href="#">
                                    <img src="img/users/5.jpg" class="rounded-circle" alt="">
                                </a>
                            </figure>
                            <div class="message-body media-body">
                                <p>There's a cheesy incarnation waiting for you no matter what you palete preferences
                                    are.</p>
                            </div>
                        </div>
                        <div class="message media">
                            <figure class="user--online">
                                <a href="#">
                                    <img src="img/users/1.jpg" class="rounded-circle" alt="">
                                </a>
                            </figure>
                            <div class="message-body media-body">
                                <p>If you are a vegan, we are sorry for you loss.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form action="javascript:void(0)" class="card-footer" method="post">
                <div class="d-flex justify-content-end">
                    <textarea class="border-0 flex-1" rows="1" placeholder="Type your message here"></textarea>
                    <button class="btn btn-icon" type="submit"><i
                            class="ik ik-arrow-right text-success"></i></button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="w-100 clearfix">
            <span class="text-center text-sm-left d-md-inline-block">Gatra Perdana Trustrue</span>
            {{-- <span class="float-none float-sm-right mt-1 mt-sm-0 text-center">Crafted with <i
                    class="fa fa-heart text-danger"></i> by <a href="http://lavalite.org/" class="text-dark"
                    target="_blank">Lavalite</a></span> --}}
        </div>
    </footer>

</div>

<script>
    $('.nav-toggle').on('click', function() {
        $('.app-sidebar').toggleClass('collapsed');
    });


    function confirmLogout() {
        Swal.fire({
            title: 'Yakin ingin logout?',
            text: "Anda akan keluar dari sistem.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }
</script>
