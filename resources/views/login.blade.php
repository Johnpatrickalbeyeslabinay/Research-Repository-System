@extends('layouts.login_layout')

@section('title', 'Login')

@section('login')

    <div class="container-fluid min-vh-100">

        <div class="row min-vh-100">

            <!-- LEFT SIDE -->
            <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center text-white">

                <div class="text-center text-lg-start">

                    <div class="display-1 mb-4">
                        ⚪
                    </div>

                    <h1 class="fw-bold display-4">
                        Research Repository
                    </h1>

                    <h3 class="fw-normal">
                        Cagayan State University - Aparri
                    </h3>

                    <h4 class="fw-light mb-5">
                        Fisheries Program
                    </h4>

                    <div class="mb-3 fs-5">
                        📚 Discover academic research
                    </div>

                    <div class="mb-3 fs-5">
                        🔍 Advanced search & filtering
                    </div>

                    <div class="mb-5 fs-5">
                        🎓 Student & faculty collaboration
                    </div>

                    <button class="btn btn-outline-light btn-lg">
                        <a href="{{ route('public') }}" class="text-decoration-none text-white">
                            Browse Public Repository →
                        </a>
                    </button>

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
                                Select your role and enter your credentials
                            </p>

                            <label class="fw-semibold mb-2">
                                Select Role
                            </label>

                            <div class="row g-2 mb-4">

                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="role" id="student" value="student" checked>

                                    <label id="studentCard" class="card border-primary text-primary h-100 text-center p-3"
                                        for="student" style="cursor:pointer;">
                                        <i id="studentIcon" class="bi bi-mortarboard fs-3"></i>
                                        <div class="fw-semibold mt-2">
                                            Student
                                        </div>
                                    </label>
                                </div>

                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="role" id="instructor" value="instructor">

                                    <label id="instructorCard" class="card h-100 text-center p-3" for="instructor"
                                        style="cursor:pointer;">
                                        <i id="instructorIcon" class="bi bi-people fs-3"></i>
                                        <div class="fw-semibold mt-2">
                                            Instructor
                                        </div>
                                    </label>
                                </div>

                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="role" id="admin" value="admin">

                                    <label id="adminCard" class="card h-100 text-center p-3" for="admin"
                                        style="cursor:pointer;">
                                        <i id="adminIcon" class="bi bi-shield-lock fs-3"></i>
                                        <div class="fw-semibold mt-2">
                                            Admin
                                        </div>
                                    </label>
                                </div>

                            </div>

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    User ID
                                </label>

                                <div class="d-flex align-items-center border rounded-4 px-3">

                                    <i class="bi bi-person me-3 text-secondary"></i>

                                    <input type="text" name="user_id" class="form-control border-0 shadow-none"
                                        placeholder="Enter your User ID" value="{{ old('user_id') }}" required>

                                </div>

                            </div>

                            <div class="mb-4">

                                <label class="form-label fw-semibold">
                                    Password
                                </label>

                                <div class="d-flex align-items-center border rounded-4 px-3">

                                    <i class="bi bi-lock me-3 text-secondary"></i>

                                    <input type="password" name="password" class="form-control border-0 shadow-none"
                                        placeholder="Enter your password" required>

                                    <button class="btn p-0 border-0">
                                        <i class="bi bi-eye text-secondary"></i>
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
        document.querySelectorAll('input[name="role"]').forEach(radio => {

            radio.addEventListener('change', function () {

                const btn = document.getElementById('loginBtn');

                const cards = [
                    document.getElementById('studentCard'),
                    document.getElementById('instructorCard'),
                    document.getElementById('adminCard')
                ];

                const icons = [
                    document.getElementById('studentIcon'),
                    document.getElementById('instructorIcon'),
                    document.getElementById('adminIcon')
                ];

                // Reset cards
                cards.forEach(card => {
                    card.classList.remove(
                        'border-primary', 'text-primary',
                        'border-success', 'text-success',
                        'border-danger', 'text-danger'
                    );
                });

                // Reset icons
                icons.forEach(icon => {
                    icon.classList.remove(
                        'text-primary',
                        'text-success',
                        'text-danger'
                    );
                });

                // Reset button
                btn.classList.remove(
                    'btn-primary',
                    'btn-success',
                    'btn-danger'
                );

                if (this.value === 'student') {

                    btn.classList.add('btn-primary');

                    studentCard.classList.add(
                        'border-primary',
                        'text-primary'
                    );

                    studentIcon.classList.add('text-primary');

                }

                if (this.value === 'instructor') {

                    btn.classList.add('btn-success');

                    instructorCard.classList.add(
                        'border-success',
                        'text-success'
                    );

                    instructorIcon.classList.add('text-success');

                }

                if (this.value === 'admin') {

                    btn.classList.add('btn-danger');

                    adminCard.classList.add(
                        'border-danger',
                        'text-danger'
                    );

                    adminIcon.classList.add('text-danger');

                }

                btn.textContent =
                    'Sign In as ' +
                    this.value.charAt(0).toUpperCase() +
                    this.value.slice(1);

            });

        });
    </script>



@endsection