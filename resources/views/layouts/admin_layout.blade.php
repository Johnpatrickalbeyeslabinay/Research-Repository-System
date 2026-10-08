<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['layoutCss' => 'resources/css/layouts/admin.css'])
</head>

<style>
    .research-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
    }

    .research-table {
        font-size: 0.8125rem;          /* 13px body text */
        color: #374151;
        margin-bottom: 0;
    }

    .research-table thead th {
        font-size: 0.6875rem;          /* 11px header */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: black;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 0.7rem 1rem;
        white-space: nowrap;
    }

    .research-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
    }

    .research-table tbody tr:last-child td { border-bottom: 0; }
    .research-table tbody tr:hover { background: #f9fafb; }

    .research-table .col-title {
        max-width: 260px;
        font-weight: 600;
        font-size: 0.8125rem;
        color: #111827;
    }

    .research-table .col-researchers {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .research-table .col-nowrap { white-space: nowrap; }

    .research-table .stat {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #4b5563;
        font-variant-numeric: tabular-nums;
    }

    .research-table .badge {
        font-size: 0.6875rem;
        font-weight: 500;
        padding: 0.35em 0.75em;
    }

    .research-table .btn-action {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        white-space: nowrap;
    }

    /* Popover text sizes */
    .research-popover h5 { font-size: 1rem; }
    .research-popover h6 { font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.03em; color: #6b7280; }
    .research-popover p,
    .research-popover small,
    .research-popover .alert { font-size: 0.8125rem; }


       .archive-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        padding: 1.5rem;
    }

    .archive-card .form-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.35rem;
    }

    .archive-card .form-control,
    .archive-card .form-select {
        font-size: 0.875rem;
        background-color: #f3f4f6;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0.6rem 0.85rem;
    }

    .archive-card .form-control:focus,
    .archive-card .form-select:focus {
        background-color: #fff;
        border-color: #86b7fe;
    }

    .archive-card .form-text { font-size: 0.75rem; }

    .dropzone-box {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 2rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s, background-color .15s;
    }

    .dropzone-box:hover,
    .dropzone-box.is-dragover {
        border-color: #0d6efd;
        background: #f5f9ff;
    }

    .dropzone-box.has-file {
        border-style: solid;
        border-color: #198754;
        background: #f3faf6;
    }

    .dropzone-box .dz-icon { font-size: 2.25rem; color: #6b7280; }
    .dropzone-box .dz-title { font-size: 0.9375rem; font-weight: 600; color: #111827; }
    .dropzone-box .dz-hint  { font-size: 0.75rem; color: #6b7280; }

    .archive-card {
    width: 100%;
    max-width: 850px;
    margin-left: auto;
    margin-right: auto;
}

.filter-bar {
    margin: 15px auto 0;
    width: 100%;
    max-width: 1100px;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

    .filter-bar .filter-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 0.04em;
        color: black;
        margin-bottom: 0.35rem;
    }

    .filter-bar .form-select {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #111827;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.5rem 2.25rem 0.5rem 0.75rem;
        min-width: 150px;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }

    .filter-bar .form-select:hover {
        background-color: #fff;
        border-color: #cbd5e1;
    }

    .filter-bar .form-select:focus {
        background-color: #fff;
        border-color: #86b7fe;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
    }

    .filter-bar #sdgToggle {
        display: block;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    .filter-bar .btn-reset {
        font-size: 0.8125rem;
        font-weight: 500;
        padding: 0.5rem 0.9rem;
        border-radius: 8px;
        color: #374151;
        background: #fff;
        border: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .filter-bar .btn-reset:hover {
        background: #f3f4f6;
        border-color: #cbd5e1;
        color: #111827;
    }

    #reportFilterForm .form-select {
    min-width: 190px;
    min-height: 44px;
}

#reportFilterForm .btn {
    min-height: 44px;
}



   @media print {

    body.printing-report * {
        visibility: hidden !important;
    }

    body.printing-report #adminPrintReport,
    body.printing-report #adminPrintReport * {
        visibility: visible !important;
    }

    body.printing-report #adminPrintReport {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        display: block !important;
    }

    body.printing-report .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    body.printing-report h5 {
        color: #072a89 !important;
    }

    body.printing-report #reportChart,
    body.printing-report #registeredUsersChart {
        height: 420px !important;
    }

    body.printing-report #searchKeywordsChart {
        height: 500px !important;
        break-before: page;
        page-break-before: always;
    }
}
</style>

<body class="text-dark" style="font-family: sans-serif, arial;
  font-size: var(--font-size-sm);            /* 14px base */
  color: var(--color-text-primary);          /* #111827 default text */
  background: #f9fafb;
  margin: 0;
  padding: 0;
  line-height: 1.5;
}">


    <button class="navbar-toggler border-0 me-2 d-lg-none" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#sidebar">
        <span class="navbar-toggler-icon"></span>
    </button>


    <!--sidebar-->

    <div class="container-fluid">
        <div class="row">

            <nav class="navbar navbar-light bg-white border-bottom d-lg-none">
                <div class="container-fluid">

                    <!-- Logo + Program -->
                    <div class="d-flex align-items-center">

                    <img width="90" height="90" decoding="async" src="{{ asset('images/logo-192.webp') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:90px;height:90px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">Research Repository System</h5>
                        <small class="text-secondary">College of Fisheries and Aquatic Science</small>
                    </div>

                </div>

                    <!-- Bell + Hamburger -->
                    <div class="d-flex align-items-center">

                        <!-- Notification -->
                        <a href="{{ route('notifications.index') }}" class="btn border-0 p-1 me-2 position-relative">
                            <i class="fa-regular fa-bell"></i>
                            @if(($unreadNotificationsCount ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.55rem;">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>

                        <!-- Hamburger -->
                        <button class="navbar-toggler border-0 p-1" type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#sidebar">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                    </div>

                </div>
            </nav>


            <aside class="col-lg-2 d-none d-lg-flex flex-column bg-white border-end p-0 position-fixed"
                style="height:100vh; width:250px; left:0; top:0;">

                <!-- Header -->
                <div class="offcanvas-header py-4 px-3 text-dark bg-white">


                    <!-- Logo + Program -->
                    <div class="d-flex align-items-center">

                    <img width="70" height="70" decoding="async" src="{{ asset('images/logo-192.webp') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:70px;height:70px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">Research Repository System</h5>
                        <small class="text-secondary">College of Fisheries and Aquatic Science</small>
                    </div>

                </div>
                </div>


                <div class="border-top p-2 d-flex align-items-center gap-2" style=" background-color: #EFF6FF">

                    <!-- ICON WITH CIRCLE BACKGROUND -->
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                        style="width: 40px; height: 35px; font-size: 18px; background: #072a89;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>

                    <!-- USER INFO -->
                    <div>
                        <h6 class="fw-bold mb-0">
                            {{ Auth::user()->name }}
                        </h6>
                        <small class="text-secondary">
                            {{ Auth::user()->role }}
                        </small>
                    </div>

                </div>

                <!-- Menu -->
                <div class="flex-grow-1 p-3">

                    <ul class="nav flex-column">

    {{-- Dashboard --}}
    <li class="nav-item">
        <a class="nav-link {{ $view === 'dashboard'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=dashboard') }}">

            <i class="fa-solid fa-book me-2"></i>
            Dashboard
        </a>
    </li>

    {{-- Manage Users --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->query('view') === 'user'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=user') }}">

            <i class="fa-regular fa-user me-2"></i>
            Manage Users
        </a>
    </li>

    {{-- Upload Research --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->query('view') === 'upload'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=upload') }}">

            <i class="fa-solid fa-upload me-2"></i>
            Upload Research
        </a>
    </li>

     {{-- Report --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->query('view') === 'report'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=report') }}">

            <i class="fa-solid fa-chart-line me-2"></i>
            Report
        </a>
    </li>

    {{-- Repository --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->routeIs('repository')
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ route('repository') }}">

            <i class="fa-solid fa-folder me-2"></i>
            Repository
        </a>
    </li>

</ul>

                </div>

               <!-- Footer -->
                <div class="border-top p-3">


                <button type="button"
                        class="rounded-2 w-100 bg-white border-0 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#manageaccount">

                    <i class="fa-solid fa-gear fa-sm me-3"></i>
                    Manage Account
                </button>

                    <form action="{{ route('logout') }}" data-turbo="false" method="POST" class="mt-3">
                        @csrf

                        <button type="submit"
                            class="btn btn-md bg-danger-subtle text-danger-emphasis border-danger-subtle rounded-3 w-100 text-start"
                            id="logoutBtn">
                            <i class="fa-solid fa-right-from-bracket me-2" id="logoutIcon"></i>
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="logoutSpinner" role="status" aria-hidden="true"></span>
                            <span id="logoutText">Logout</span>
                        </button>

                    </form>

                </div>

            </aside>

        </div>
    </div>



    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar">

        <div class="offcanvas-header text-white">


            <div class="offcanvas-header py-4 px-3 text-dark bg-white">


                <div class="d-flex align-items-center">

                    <img width="90" height="90" decoding="async" src="{{ asset('images/logo-192.webp') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:90px;height:90px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">Research Repository System</h5>
                        <small class="text-secondary">College of Fisheries and Aquatic Science</small>
                    </div>

                </div>

            </div>



            <button class="btn-close btn-close-dark" data-bs-dismiss="offcanvas"></button>

        </div>

        <div class="border-top p-2 d-flex align-items-center gap-2" style=" background-color: #EFF6FF">

            <!-- ICON WITH CIRCLE BACKGROUND -->
            <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                style="width: 45px; height: 45px; font-size: 18px; background: #072a89">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <!-- USER INFO -->
            <div>
                <h6 class="fw-bold mb-0">
                    {{ Auth::user()->name }}
                </h6>
                <small class="text-secondary">
                    {{ Auth::user()->role }}
                </small>
            </div>

        </div>

        <div class="offcanvas-body">

             <ul class="nav flex-column">

    {{-- Dashboard --}}
    <li class="nav-item">
        <a class="nav-link {{ $view === 'dashboard'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=dashboard') }}">

            <i class="fa-solid fa-book me-2"></i>
            Dashboard
        </a>
    </li>

    {{-- Manage Users --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->query('view') === 'user'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=user') }}">

            <i class="fa-regular fa-user me-2"></i>
            Manage Users
        </a>
    </li>

    {{-- Upload Research --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->query('view') === 'upload'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=upload') }}">

            <i class="fa-solid fa-upload me-2"></i>
            Upload Research
        </a>
    </li>

    {{-- Report --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->query('view') === 'report'
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ url('/admin?view=report') }}">

            <i class="fa-solid fa-chart-line me-2"></i>
            Report
        </a>
    </li>

    {{-- Repository --}}
    <li class="nav-item mt-2">
        <a class="nav-link {{ request()->routeIs('repository')
            ? 'active text-white bg-primary rounded'
            : 'text-dark bg-light rounded' }}"
            href="{{ route('repository') }}">

            <i class="fa-solid fa-folder me-2"></i>
            Repository
        </a>
    </li>

</ul>

            <hr>



        </div>

        <!-- Footer -->
                <div class="border-top p-3">


                <button type="button"
                        class="rounded-2 w-100 bg-white border-0 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#manageaccount">

                    <i class="fa-solid fa-gear fa-sm me-3"></i>
                    Manage Account
                </button>

                    <form action="{{ route('logout') }}" data-turbo="false" method="POST" class="mt-3">
                        @csrf

                        <button type="submit"
                            class="btn btn-md bg-danger-subtle text-danger-emphasis border-danger-subtle rounded-3 w-100 text-start"
                            id="logoutBtn">
                            <i class="fa-solid fa-right-from-bracket me-2" id="logoutIcon"></i>
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="logoutSpinner" role="status" aria-hidden="true"></span>
                            <span id="logoutText">Logout</span>
                        </button>

                    </form>

                </div>

    </div>



    <div class="container-fluid">
        <div class="row">

            <aside class="col-lg-2 d-none d-lg-flex flex-column">
                ...
            </aside>

            <main class="col-12 col-lg-10 p-4">
                @yield('admin')
            </main>

        </div>
    </div>










@include('partials.flash')

@include('components.research.welcome-modal')

<x-chat.widget />



</body>

</html>
