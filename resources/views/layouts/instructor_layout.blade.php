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
  .navbar-toggler-icon {
    filter: invert(100%);
  }

  .nav-link {
    transition: all 0.3s ease-in-out;
    border-radius: 10px;
    border: .5px solid rgba(166, 169, 170, 0.42);
    color: black !important;
  }

  .nav-link.active {
    border: none;
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

  <nav class="navbar navbar-dark fixed-top shadow-sm"
    style="background: linear-gradient(to right, #14b8a6, #06b6d4, #3b82f6);">

    <div class="d-flex align-items-center ps-3">

      <button class="navbar-toggler border-0 me-2 navbar navbar-white" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#sidebar">
        <span class="navbar-toggler-icon"></span>
      </button>

      <i class="fa-solid fa-fish-fins fa-2xl ps-2" style="color: #67e8f9;"></i>

      <div class="text-white m-0 flex-column ps-3">
        <h5 class="offcanvas-title fw-bolder text-white"> Instructor Dashboard</h5>
        <span class="text-white-50"> FishLab Repository System</span>
      </div>

    </div>
  </nav>

  <!--sidebar-->

  <div class="offcanvas offcanvas-start text-white" tabindex="-1" id="sidebar" style="background: #fff; max-width: 60%">

    <div class="offcanvas-header text-white" style="background: linear-gradient(to right, #14b8a6, #06b6d4);">
      <h5 class="offcanvas-title fw-bolder text-white"><i class="fa-solid fa-fish-fins text-info"></i> Fish Lab System
      </h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>


    <div class="offcanvas-body">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link {{ request()->query('instructor_btn') === 'Upload' ? 'active text-white' : 'text-dark' }}"
            href="{{ url('/instructor_dashboard?instructor_btn=Upload') }}">
            <i
              class="fa-solid fa-book {{ request()->query('instructor_dashboard') === 'Upload' ? 'text-white' : '' }}"></i>
            Upload File
          </a>
        </li>
      </ul>

      <hr>

      <!-- logout btn-->
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn text-danger bg-none form-control text-start">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </form>
    </div>




    <div class="offcanvas-header flex-column align-items-start bg-light border-top">
      <h5 class="offcanvas-title fw-bolder text-dark">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
      <span class="text-secondary">{{ Auth::user()->stud_id }}</span>
    </div>
  </div>


  <br><br><br>






  <!-- Design sa content.blade.php -->
  <div class="container mt-4">
    @yield('instructor')
  </div>






  <!-- FOOTER -->
  <footer class="text-white text-center  fixed-bottom p-2"
    style="background: linear-gradient(to right, #164e63, #1e3a8a, #164e63 );">
    <div class="m-0 flex-column" style="color: #cffafe;">
      <h6 class="">Cagayan State University Aparri Campus</h6>
      <h6 style="">College of Fisheries and Aquatic Sciences</h6>
    </div>
  </footer>





  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Expand/Collapse Button -->
  <script>
    const expandBtn = document.getElementById("expandAllBtn");
    let expanded = false;

    expandBtn.addEventListener("click", function () {
      const collapses = document.querySelectorAll(".collapse");

      collapses.forEach(collapseEl => {
        const bsCollapse = new bootstrap.Collapse(collapseEl, {
          toggle: false
        });

        if (!expanded) {
          bsCollapse.show();
        } else {
          bsCollapse.hide();
        }
      });

      expanded = !expanded;

      expandBtn.innerHTML = expanded
        ? '<i class="fa-solid fa-folder-minus"></i> Collapse All'
        : '<i class="fa-regular fa-folder-open"></i> Expand All';
    });
  </script>

</body>

</html>