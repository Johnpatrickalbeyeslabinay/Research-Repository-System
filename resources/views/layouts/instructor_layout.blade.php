<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['layoutCss' => 'resources/css/layouts/instructor.css'])
</head>

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
                        <small class="text-secondary">CSU Aparri - College of Fisheries and Aquatic Science</small>
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
                        <small class="text-secondary">CSU Aparri - College of Fisheries and Aquatic Science</small>
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

                            <a class="nav-link {{ $view == 'dashboard' ? 'active text-white bg-primary rounded' : 'text-dark bg-light rounded' }}"
                            href="{{ url('/instructor?view=dashboard') }}">

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


        <button type="button"
                        class="rounded-2 w-100 bg-white border-0 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#manageaccount">

                    <i class="fa-solid fa-gear fa-sm me-3"></i>
                    Manage Account
                </button>

          <form action="{{ route('logout') }}" data-turbo="false" method="POST" class="mt-3">
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

    <div class="offcanvas-header text-white" style="background:;">

      <div class="d-flex align-items-center">

                    <img width="90" height="90" decoding="async" src="{{ asset('images/logo-192.webp') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:90px;height:90px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">Research Repository System</h5>
                        <small class="text-secondary">CSU Aparri - College of Fisheries and Aquatic Science</small>
                    </div>

                </div>

      <button class="btn-close btn-close-dark" data-bs-dismiss="offcanvas"></button>

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

    <div class="offcanvas-body">

      <ul class="nav flex-column">

                        <li class="nav-item">

                            <a class="nav-link {{ $view == 'dashboard' ? 'active text-white bg-primary rounded' : 'text-dark bg-light rounded' }}"
                            href="{{ url('/instructor?view=dashboard') }}">

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

            <button class="btn btn-outline-danger w-100 text-start">

              <i class="fa-solid fa-right-from-bracket me-2"></i>

              Logout

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
        @yield('instructor')
      </main>

    </div>
  </div>




@include('partials.flash')

<x-chat.widget />




</body>

</html>
