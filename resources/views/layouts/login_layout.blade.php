<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>
<style>
     .captcha-field .captcha-box {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 42px;
        padding: 0 12px;
        background: #f1f4f9;
        border: 1px dashed #c3cddd;
        border-radius: 8px;
        user-select: none;
        -webkit-user-select: none;
    }

    .captcha-field .captcha-box span {
        display: inline-block;
        font-family: Consolas, "Courier New", monospace;
        font-size: 19px;
        font-weight: 600;
        line-height: 1;
        color: #1e3a8a;
    }

    .captcha-field .captcha-box span:nth-child(odd) {
        transform: rotate(-6deg);
    }

    .captcha-field .captcha-box span:nth-child(even) {
        transform: rotate(5deg) translateY(1px);
    }

    .captcha-field .captcha-refresh {
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: 8px;
        color: #1e3a8a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .captcha-field .captcha-refresh:hover {
        background: #e8edf7;
    }

    .captcha-field .form-control {
        height: 42px;
        border-radius: 8px;
        font-size: 14px;
        letter-spacing: 1px;
    }

    #loginBtn,
#loginBtn:hover,
#loginBtn:focus,
#loginBtn:disabled {
    background-color: #00008b; /* dark blue */
    border-color: #00008b;
    color: #fff;
}
#loginBtn:disabled { opacity: 0.85; }

</style>

<body class="text-dark" style=" background-color: #1D3C9D;  font-family: sans-serif;">







    <!-- Design sa content.blade.php -->
    <div class="container mt-4">
        @yield('login')
    </div>






    <!-- FOOTER -->
    <footer class="text-center">
    </footer>





</body>

</html>
