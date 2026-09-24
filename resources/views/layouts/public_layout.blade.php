<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'PUBLIC')</title>

  <!--icons-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

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

  /* Sticky footer, not a FIXED one.
     The footer was .fixed-bottom, which lifts it out of the document flow
     and parks it permanently over the last ~80px of the page — on
     research/show that covered the View PDF / Download / Ask about this
     research buttons entirely, with no way to scroll past it.
     A flex column instead: the content region grows to fill a short page
     (so the footer still sits at the bottom with no content above it) and
     the footer is simply pushed below on a long one. */
  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .page-content {
    flex: 1 0 auto;
    /* Clears the .fixed-top navbar, which IS still out of flow. Replaces
       three <br> tags that approximated the same thing. */
    padding-top: 88px;
  }

  footer {
    flex-shrink: 0;
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

 <nav class="navbar navbar-expand-lg mb-5 navbar-light bg-white fixed-top shadow-sm border-bottom">

    <div class="container-fluid justify-content-between">

        <!-- Logo -->
        <div class="d-flex align-items-center">

            <!-- Logo + Program -->
            <div class="d-flex align-items-center">

                <img src="{{ asset('images/logo.png') }}"
                    alt="CSU Aparri Logo"
                    class="me-3 rounded-circle"
                    style="width:90px;height:90px;object-fit:cover;">

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





  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

</body>

</html>
