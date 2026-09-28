<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['layoutCss' => 'resources/css/layouts/public.css'])
</head>

<body class="text-dark" style="font-family: sans-serif, arial;
  font-size: var(--font-size-sm);            /* 14px base */
  color: var(--color-text-primary);          /* #111827 default text */
  background: #f9fafb;
  margin: 0;
  padding: 0;
  line-height: 1.5;
}">

  <!--navbar-->

 <nav class="navbar navbar-expand-lg mb-5 navbar-light bg-white fixed-top shadow-sm border-bottom">

    <div class="container-fluid justify-content-between">

        <!-- Logo -->
        <div class="d-flex align-items-center">

            <!-- Logo + Program -->
            <div class="d-flex align-items-center">

                <img src="{{ asset('images/logo.png') }}"
                    alt="CSU Aparri Logo"
                    class="me-3 rounded-circle"
                    style="width:70px;height:70px;object-fit:cover;">

                <div class="d-flex flex-column">
                    <h5 class="fw-bold mb-0">CSU APARRI</h5>
                    <small class="text-secondary">Fisheries Program</small>
                </div>

            </div>

        </div>

        <!-- Login - pinakadulo kanan -->
        <div>
            <a href="{{ route('login') }}"
                class="btn text-white px-3 py-2 me-3"
                style="background:navy;">
                <i class="fa-solid fa-arrow-right me-2"></i>
                Login
            </a>
        </div>

    </div>

</nav>



  <!-- Design sa content.blade.php -->
  <div class="container page-content pt-4 pb-5">
    @yield('public')
  </div>






  <!-- FOOTER -->
  <footer class="text-white text-center p-2" style="background: navy;">
    <div class="m-0 flex-column" style="color: #cffafe;">
      <h6 class="">2026 Cagayan State University Aparri Campus</h6>
      <h6 style="">College of Fisheries and Aquatic Sciences</h6>
    </div>
  </footer>






</body>

</html>
