<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['layoutCss' => 'resources/css/layouts/public.css'])
</head>

<body class="app-shell text-dark">
    <nav class="navbar navbar-light bg-white fixed-top border-bottom site-navbar">
        <div class="container-fluid site-navbar-inner">
            <a href="{{ route('home') }}" class="site-brand" aria-label="CSU Aparri repository home">
                <img src="{{ asset('images/logo.png') }}" alt="CSU Aparri Logo" class="site-logo">
                <span class="site-brand-copy">
                    <strong>CSU APARRI</strong>
                    <small>Fisheries Program</small>
                </span>
            </a>

            @auth
                @php
                    $dashboardRoute = match(Auth::user()->role) {
                        'admin' => 'admin',
                        'instructor' => 'instructor',
                        'student' => 'student',
                        default => 'home',
                    };
                @endphp
                <a href="{{ route($dashboardRoute) }}" class="btn site-nav-action">
                    <i data-lucide="layout-dashboard" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn site-nav-action">
                    <i data-lucide="log-in" aria-hidden="true"></i>
                    <span>Sign in</span>
                </a>
            @endauth
        </div>
    </nav>

    <div class="container page-content repository-page-shell">
        @yield('public')
    </div>

    <footer class="site-footer text-center">
        <div>
            <p>2026 Cagayan State University Aparri Campus</p>
            <p>College of Fisheries and Aquatic Sciences</p>
        </div>
    </footer>
</body>

</html>
