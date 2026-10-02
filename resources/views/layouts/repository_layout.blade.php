<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['layoutCss' => 'resources/css/layouts/repository.css'])
</head>

<body class="app-shell text-dark">
    <nav class="navbar navbar-light bg-white fixed-top border-bottom site-navbar">
        <div class="container-fluid site-navbar-inner">
            <a href="{{ route('repository') }}" class="site-brand" aria-label="CSU Aparri research repository">
                <img src="{{ asset('images/logo.png') }}" alt="CSU Aparri Logo" class="site-logo">
                <span class="site-brand-copy">
                    <strong>Research Repository System</strong>
                    <small>CSU Aparri - College of Fisheries and Aquatic Science</small>
                </span>
            </a>

            @php
                $dashboardRoute = match(Auth::user()->role) {
                    'admin' => 'admin',
                    'instructor' => 'instructor',
                    'student' => 'student',
                    default => 'home',
                };
            @endphp

            <a href="{{ route($dashboardRoute) }}" class="btn site-nav-action site-nav-action-secondary">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </nav>

    <div class="container page-content repository-page-shell">
        @yield('repository')
    </div>

    <footer class="site-footer text-center">
        <div>
            <p>2026 Cagayan State University Aparri Campus</p>
            <p>College of Fisheries and Aquatic Sciences</p>
        </div>
    </footer>

    <x-chat.widget />
</body>

</html>
