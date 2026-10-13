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
   class="btn btn-outline-light btn-lg px-2 py-1 fw-normal"
   onclick="showLoading(this)">

    <span class="spinner-border spinner-border-sm d-none me-2"
          role="status"
          aria-hidden="true"></span>

    <span class="browse-text">
        Browse Public Repository
    </span>

    <i class="fa-solid fa-arrow-right ms-2"></i>

</a>

    </div>

</div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center py-5">

                <div class="card shadow border-0 w-100" style="max-width: 400px;">



                    <div class="card-body p-4">

                        <form action="{{ route('login.submit') }}" data-turbo="false" method="POST">
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

                            <div class="mb-3">
                            <div
                                class="cf-turnstile"
                                data-sitekey="{{ config('services.turnstile.site_key') }}"
                            ></div>
                        </div>



                        <div class="mb-3 captcha-field">

                            <label class="form-label">Security code</label>

                            <div class="d-flex align-items-center gap-2">

                                <div class="captcha-box flex-grow-1" id="captchaCode">
                                    @foreach(str_split($captchaCode) as $character)
                                        <span>{{ $character }}</span>
                                    @endforeach
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-light border captcha-refresh"
                                    onclick="refreshCaptcha()"
                                    aria-label="Refresh CAPTCHA"
                                    title="Refresh"
                                >
                                    <i class="fa-solid fa-rotate"></i>
                                </button>

                            </div>

                            <input
                                type="text"
                                name="captcha"
                                class="form-control mt-2 @error('captcha') is-invalid @enderror"
                                placeholder="Enter the code"
                                maxlength="6"
                                autocomplete="off"
                                required
                            >

                            @error('captcha')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                            <button type="submit" class="btn btn-primary w-100 mb-4" id="loginBtn">
                                <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
                                <span id="loginText">Sign In</span>
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
document.getElementById('loginBtn').closest('form').addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    document.getElementById('loginSpinner').classList.remove('d-none');
    document.getElementById('loginText').textContent = 'Signing in...';
    setTimeout(() => btn.disabled = true, 0); // disable after the submit starts
});
</script>

   <script>
// Runs inline: under Turbo there's no DOMContentLoaded on a page visit, and
// this script sits after the elements it binds.
(function () {

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

})();
</script>



<script>
function refreshCaptcha() {
    fetch('{{ route('captcha.refresh') }}')
        .then(response => response.json())
        .then(data => {
            const captcha = document.getElementById('captchaCode');

            captcha.innerHTML = '';

            [...data.captcha].forEach(character => {
                const span = document.createElement('span');
                span.textContent = character;
                captcha.appendChild(span);
            });
        });
}
</script>

<script>
function showLoading(button) {
    const spinner = button.querySelector('.spinner-border');

    spinner.classList.remove('d-none');
}
</script>

@endsection
