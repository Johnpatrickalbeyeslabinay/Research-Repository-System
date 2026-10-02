<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['layoutCss' => 'resources/css/layouts/home.css'])
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

    <nav class="navbar navbar-light bg-white text-dark fixed-top shadow-sm home-navbar">

        <div class="container-fluid home-navbar-inner">

            <!-- Title -->
            <div class="d-flex align-items-center home-brand">

                    <img width="70" height="70" decoding="async" src="{{ asset('images/logo-192.webp') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle home-brand-logo">

                    <div class="d-flex flex-column home-brand-copy">
                        <h5 class="fw-bold mb-0">CSU APARRI</h5>
                        <small class="fw-bold text-secondary">Fisheries Program</small>
                    </div>

                </div>

            <!-- login and register buttons -->
            <div class="ms-auto d-flex gap-2 home-nav-actions">
                @if (Route::has('public'))
                    <a href="{{ route('public') }}" class="btn home-browse-button">Browse Repository</a>
                @else
                    <a href="#" class="btn">Browse Repository</a>
                @endif

                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn home-login-button">Student Login</a>
                @else
                    <a href="#" class="btn btn-outline-light text-white fw-bold px-2 py-1">Student Login</a>
                @endif
            </div>

        </div>
    </nav>

    <br><br><br>




    <!-- Design sa content.blade.php -->
    <div class="container mt-4">
        @yield('home')
    </div>






    <!-- FOOTER -->
    <footer class="text-center bg-light">

        <div class="text-secondary m-0 flex-column p-5">
            <h6>Cagayan State University - Aparri Campus</h6>
            <p>Fisheries Program Research Repository</p>
        </div>


    </footer>






</body>

</html>
