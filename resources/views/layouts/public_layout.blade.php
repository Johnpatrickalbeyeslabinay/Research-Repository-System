<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'PUBLIC')</title>

  <!--icons-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<style>
  .navbar-toggler-icon {
    filter: invert(100%);
  }

  .nav-link {
    transition: all 0.3s ease-in-out;
  }

  .nav-link.active {
    color: white !important;
    background-color: #1E90FF;
    font: bold;
    border-radius: 10px;
  }

  nav {
    z-index: 1000;
  }

  nav a {
    text-decoration: none;
  }
</style>

<body class="text-dark"
  style="background: linear-gradient(to right, #ecfeff, #f0fdfa, #eff6ff);  font-family: sans-serif;">

  <!--navbar-->

  <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm"
    style="background: linear-gradient(to right, #14b8a6, #06b6d4, #3b82f6);">

    <div class="container-fluid">

      <!-- Title -->
      <div class="navbar-brand ms-3 d-flex align-items-center">
        <i class="fa-solid fa-fish-fins fa-2xl me-2" style="color: #67e8f9;"></i>
        <div>
          <div class="fw-bold">Research Repository System</div>
          <small class="text-white-50">CSU Aparri-Fisheries Program</small>
        </div>
      </div>

      <!-- login and register buttons -->
      <div class="ms-auto d-flex gap-2">

        @if (Route::has('login'))
          <a href="{{ route('login') }}" class="btn btn-info">Login</a>
        @else
          <a href="#" class="btn btn-outline-light text-white fw-bold"
            style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);">Login</a>
        @endif
      </div>

    </div>
  </nav>



  <br><br><br>






  <!-- Design sa content.blade.php -->
  <div class="container mt-4">
    @yield('public')
  </div>






  <!-- FOOTER -->
  <footer class="text-white text-center  fixed-bottom p-2" style="background: navy;">
    <div class="m-0 flex-column" style="color: #cffafe;">
      <h6 class="">2026 Cagayan State University Aparri Campus</h6>
      <h6 style="">College of Fisheries and Aquatic Sciences</h6>
    </div>
  </footer>





  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>