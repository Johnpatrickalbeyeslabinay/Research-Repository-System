<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'example lang')</title>

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<style>
  nav{
    z-index: 1000;
  }

.nav-link {
    transition: all 0.3s ease-in-out;
  }
   .nav-link.active {
    color: white!important;
    background-color: #1E90FF;
    font: bold;
    border-radius: 10px;
  }
  nav a{
    text-decoration: none;
  }
</style>

<body class="text-dark" style="background: linear-gradient(to right, #ecfeff, #f0fdfa, #eff6ff);  font-family: sans-serif;">

<!--navbar-->

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm"
      style="background: linear-gradient(to right, #14b8a6, #06b6d4, #3b82f6);">

  <div class="container-fluid">

    <!-- Title -->
    <div class="navbar-brand ms-3 d-flex align-items-center">
      <i class="fa-solid fa-fish-fins fa-2xl me-2" style="color: #67e8f9;"></i>
      <div>
        <div class="fw-bold">Admin Dashboard</div>
        <small class="text-white-50">FishLab Repository System</small>
      </div>
    </div>

    <!-- login and register buttons -->
    <div class="ms-auto d-flex gap-2">

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn text-light shadow-sm form-control text-start" style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
    </form>
    </div>

  </div>
</nav>

<br><br><br>




    <!-- Design sa content.blade.php -->
    <div class="container mt-4">
        @yield('admin_dashboard')
    </div>






    <!-- FOOTER -->
    <footer class="text-white text-center  fixed-bottom p-2" style="background: linear-gradient(to right, #164e63, #1e3a8a, #164e63 );">
        <div class="m-0 flex-column" style="color: #cffafe;">
            <h6 class="">Cagayan State University Aparri Campus</h6>
            <h6 style="">College of Fisheries and Aquatic Sciences</h6>
        </div>
    </footer>





    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
