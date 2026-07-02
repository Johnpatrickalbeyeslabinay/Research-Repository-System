<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<style>
    nav {
        z-index: 1000;
    }

    /* --- General Page Styles --- */
    :root {
        --primary-color: #0056b3;
        --secondary-color: #f8f9fa;
        --text-color: #333;
        --card-height: 250px;
    }

    /* --- Main Content --- */
    main.main-wrap {
        flex: 1;
        text-align: center;
        padding: 2rem;
    }

    /* --- Flip Card Grid --- */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    /* Container for the 3D effect */
    .flip-card {
        background-color: transparent;
        width: 100%;
        height: var(--card-height);
        perspective: 1000px;
        /* Gives the 3D depth */
        border: none;
        cursor: pointer;
        padding: 0;
        /* Reset button padding */
    }

    /* The inner container that holds the front and back */
    .flip-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.6s;
        transform-style: preserve-3d;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    /* Class added via JS to trigger flip */
    .flip-card.is-flipped .flip-inner {
        transform: rotateY(180deg);
    }

    /* Common styles for front and back */
    .flip-face {
        position: absolute;
        width: 100%;
        height: 100%;
        -webkit-backface-visibility: hidden;
        /* Safari */
        backface-visibility: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 20px;
        box-sizing: border-box;
    }

    /* Front face styling */
    .flip-front {
        background-color: white;
        color: black;
    }

    .flip-front img {
        width: 80px;
        height: 80px;
        margin-bottom: 15px;
    }

    /* Back face styling (The Message) */
    .flip-back {
        background-color: var(--primary-color);
        color: white;
        transform: rotateY(180deg);
    }

    .flip-back p {
        font-size: 1rem;
        line-height: 1.5;
        margin: 0;
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
                @if (Route::has('public'))
                    <a href="{{ route('public') }}" class="btn text-white">Browse Repository</a>
                @else
                    <a href="#" class="btn btn-outline-light">Browse Repository</a>
                @endif

                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-info">Student Login</a>
                @else
                    <a href="#" class="btn btn-outline-light text-white fw-bold"
                        style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);">Register</a>
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
</body>

</html>