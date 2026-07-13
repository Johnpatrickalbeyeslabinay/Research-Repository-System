<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'example lang')</title>

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<style>

</style>

<body class="text-dark"
    style="background: linear-gradient(to right, #ecfeff, #f0fdfa, #eff6ff);  font-family: sans-serif;">



    <br><br><br>




    <!-- Design sa content.blade.php -->
    <div class="container mt-4">
        @yield('admin')
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

</body>

</html>
