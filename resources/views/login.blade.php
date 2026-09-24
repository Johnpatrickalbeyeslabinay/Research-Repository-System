@extends('layouts.login_layout')

@section('title', 'Login')

@section('login')

    <div class="container-fluid min-vh-100">

        <div class="row min-vh-100">

            <!-- LEFT SIDE -->
            <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center text-white">

    <div class="text-center text-lg-start">

        <div class="mb-4">
            <img src="{{ asset('images/cfas.png') }}"
                 alt="Cagayan State University - Aparri"
                 class="img-fluid"
                 style="max-width: 180px; height: auto;">
        </div>

        <h1 class="fw-bold display-4">
            Research Repository
        </h1>

        <h3 class="fw-normal">
            Cagayan State University - Aparri
        </h3>

        <h4 class="fw-light mb-5">
            Bachelor of Science in Fisheries and Aquatic Sciences
        </h4>

        <div class="mb-3 fs-5">
            <h5>
                <i class="fa fa-book me-2" aria-hidden="true"></i>
                Where Fisheries Research Comes Together
            </h5>
        </div>

        <a href="{{ route('public') }}"
           class="btn btn-outline-light btn-lg px-2 py-1 fw-normal">
            Browse Public Repository
            <i class="fa-solid fa-arrow-right ms-2"></i>
        </a>

    </div>

</div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center py-5">

                <div class="card shadow border-0 w-100" style="max-width: 400px;">



                    <div class="card-body p-4">

                        <form action="{{ route('login.submit') }}" method="POST">
                            @csrf

                            <h2 class="text-center fw-bold">
                                Sign In
                            </h2>

                            <p class="text-center text-secondary mb-4">
                                Use your User ID or email address to sign in
                            </p>



                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    User ID or Email
                                </label>

                                <div class="d-flex align-items-center border rounded-4 px-3">

                                    <i class="fa-solid fa-user fa-sm"></i>

                                    <input type="text" name="login" class="form-control border-0 shadow-none"
                                        placeholder="Enter your user id or email" value="{{ old('login') }}" required>

                                </div>

                            </div>

                            <div class="mb-4">

                                <label class="form-label fw-semibold">
                                    Password
                                </label>

                                <div class="d-flex align-items-center border rounded-4 px-3">

                                    <i class="fa-solid fa-user-lock fa-sm"></i>

                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control border-0 shadow-none"
                                        placeholder="Enter your password"
                                        required>

                                    <button
                                        type="button"
                                        id="togglePassword"
                                        class="btn btn-sm border-0 text-secondary">
                                        Show
                                    </button>

                                </div>

                            </div>


                            <button type="submit" class="btn btn-primary w-100 mb-4" id="loginBtn">
                                Sign In
                            </button>

                            @if ($errors->any())

                                <div class="alert alert-danger">

                                    {{ $errors->first() }}

                                </div>

                            @endif

                        </form>

                    </div>


                </div>

            </div>

        </div>

    </div>



   <script>
document.addEventListener('DOMContentLoaded', function () {

    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');

    toggle.addEventListener('click', function () {

        if (password.type === 'password') {
            password.type = 'text';
            toggle.textContent = 'Hide';
        } else {
            password.type = 'password';
            toggle.textContent = 'Show';
        }

    });

});
</script>



@endsection
