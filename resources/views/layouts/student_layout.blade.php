<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<style>
    .nav-pills .nav-link {
        color: black;
        background: #fff;
        transition: .2s;
    }

    .nav-pills .nav-link:hover {
        background: #f8f9fa;
    }

    .nav-pills .nav-link.active {
        background: #f3f4f6;
        color: #374151;
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


                    <div class="d-flex align-items-center">

                        <i data-lucide="fish" class="me-3 rounded-circle p-2 text-white"
                            style="width: 36px; height: 36px; background: linear-gradient(135deg, #1e3a8a, #0d9488);">
                        </i>

                        <div class="d-flex flex-column">
                            <h5 class="fw-bold mb-0">CSU APARRI</h5>
                            <small class="text-secondary">Fisheries Program</small>
                        </div>

                    </div>
                    <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebar">

                        <span class="navbar-toggler-icon"></span>

                    </button>


                </div>
            </nav>


            <aside class="col-lg-2 d-none d-lg-flex flex-column bg-white border-end p-0 position-fixed"
                style="height:100vh; width:250px; left:0; top:0;">

                <!-- Header -->
                <div class="offcanvas-header py-4 px-3 text-dark bg-white">


                    <div class="d-flex align-items-center">

                        <i class="fa-solid fa-fish-fins fs-3 me-3 rounded-circle p-2 text-white"
                            style=" background: linear-gradient(135deg, #1e3a8a, #0d9488);"></i>

                        <div class="d-flex flex-column">

                            <h5 class="fw-bold mb-0">
                                CSU APARRI
                            </h5>

                            <small class="text-secondary">
                                Fisheries Program
                            </small>
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

                        <li class="nav-item">

                            <a class="nav-link {{ request()->query('view') === 'subjects' ? 'active text-white bg-primary rounded' : 'text-dark' }}"
                                href="{{ url('/student_dashboard?view=subjects') }}">

                                <i class="fa-solid fa-book me-2"></i>

                                Dashboard

                            </a>

                        </li>

                        <li class="nav-item mt-2">

                            <a class="nav-link active bg-primary rounded text-dark bg-light"
                                href="{{ route('repository')  }}">

                                <i class="fa-solid fa-folder me-2"></i>

                                Repository

                            </a>

                        </li>

                    </ul>

                </div>

                <!-- Footer -->
                <div class="border-top p-3">

                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf

                        <button class="btn btn-outline-danger w-100 text-start">

                            <i class="fa-solid fa-right-from-bracket me-2"></i>

                            Logout

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

                    <i class="fa-solid fa-fish-fins fs-3 me-3 rounded-circle p-2 text-white"
                        style=" background: linear-gradient(135deg, #1e3a8a, #0d9488);"></i>

                    <div class="d-flex flex-column">

                        <h5 class="fw-bold mb-0">
                            CSU APARRI
                        </h5>

                        <small class="text-secondary">
                            Fisheries Program
                        </small>
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

                <li class="nav-item">

                    <a class="nav-link {{ request()->query('view') === 'subjects' ? 'active text-white bg-primary rounded' : 'text-dark' }}"
                        href="{{ url('/student_dashboard?view=subjects') }}">

                        <i class="fa-solid fa-book me-2"></i>

                        Dashboard

                    </a>

                </li>

                <li class="nav-item mt-2">

                     <a class="nav-link active bg-primary rounded text-dark bg-light"
                                href="{{ route('repository')  }}">

                                <i class="fa-solid fa-folder me-2"></i>

                                Repository

                            </a>

                </li>
            </ul>

            <hr>



        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button class="btn btn-outline-danger w-100 text-start">

                <i class="fa-solid fa-right-from-bracket me-2"></i>

                Logout

            </button>

        </form>

    </div>



    <div class="container-fluid">
        <div class="row">

            <aside class="col-lg-2 d-none d-lg-flex flex-column">
                ...
            </aside>

            <main class="col-12 col-lg-10 p-4">
                @yield('student')
            </main>

        </div>
    </div>










    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>
