<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'PUBLIC')</title>

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

  <!--navbar-->

  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm border-bottom">

    <div class="container-fluid">

      <!-- Logo -->
      <div class="d-flex align-items-center">
        <i data-lucide="fish" class="me-3 rounded-circle p-2 text-white"
          style="width:36px;height:36px;background:linear-gradient(135deg,#1e3a8a,#0d9488);">
        </i>

        <div class="d-flex flex-column">
          <h5 class="fw-bold mb-0">CSU APARRI</h5>
          <small class="text-secondary">Fisheries Program</small>
        </div>
      </div>

      <!-- Login -->
      <div>
        <a href="{{ route('login') }}" class="btn text-white px-3 py-2 me-3" style="background:navy;">
          <i class="fa-solid fa-arrow-right me-2"></i>
          Login
        </a>
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
  <script src="https://unpkg.com/lucide@latest"></script>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>
