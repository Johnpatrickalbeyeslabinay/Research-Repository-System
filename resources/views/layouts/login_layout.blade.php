<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LOGIN')</title>

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>



<body class="text-dark" style=" background-color: #1D3C9D;  font-family: sans-serif;">







    <!-- Design sa content.blade.php -->
    <div class="container mt-4">
        @yield('login')
    </div>






    <!-- FOOTER -->
    <footer class="text-center">
    </footer>





    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
