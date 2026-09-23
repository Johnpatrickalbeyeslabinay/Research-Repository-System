<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOME</title>

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<style>
    nav {
        z-index: 1000;
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

    <!--navbar-->

    <nav class="navbar navbar-expand-lg navbar-light bg-white text-dark fixed-top shadow-sm">

        <div class="container-fluid">

            <!-- Title -->
            <div class="d-flex align-items-center">

                    <img src="{{ asset('images/logo.png') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:70px;height:70px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">CSU APARRI</h5>
                        <small class="fw-bold text-secondary">Fisheries Program</small>
                    </div>

                </div>

            <!-- login and register buttons -->
            <div class="ms-auto d-flex gap-2">
                @if (Route::has('public'))
                    <a href="{{ route('public') }}" class="btn border border-dark text-dark px-2 py-1">Browse Repository</a>
                @else
                    <a href="#" class="btn">Browse Repository</a>
                @endif

                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn text-white px-2 py-1 me-3" style="background:navy;">Student Login</a>
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





    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <script src="https://unpkg.com/lucide@latest"></script>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>
