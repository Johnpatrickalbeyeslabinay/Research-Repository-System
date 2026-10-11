@if (session('show_welcome_modal') && auth()->check())

    @php
        $user = auth()->user();

        switch ($user->role) {
            case 'student':
                $welcomeMessage = 'Your research submissions, notifications, and repository resources are ready for you.';
                break;

            case 'instructor':
                $welcomeMessage = 'Your research supervision, student submissions, and notifications are ready for you.';
                break;

            case 'admin':
                $welcomeMessage = 'Your research management tools, reports, and system notifications are ready for you.';
                break;

            default:
                $welcomeMessage = 'Welcome to the CSU Aparri Research Repository.';
        }
    @endphp

    {{-- ===================== STYLES ===================== --}}
    <style>
        #welcomeModal {
            --wm-primary: #0b2a6b;
            --wm-primary-dark: #0b2a6b;
            --wm-radius: 1.5rem;
        }

        /* Blurred backdrop */
        .modal-backdrop.show {
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            background-color: rgba(10, 20, 40, .55);
            opacity: 1;
        }

        #welcomeModal .modal-content {
            border-radius: var(--wm-radius);
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .3);
        }

        /* Animated entrance */
        #welcomeModal.fade .modal-dialog {
            transform: translateY(40px) scale(.92);
            transition: transform .45s cubic-bezier(.34, 1.56, .64, 1), opacity .3s ease;
        }
        #welcomeModal.show .modal-dialog {
            transform: translateY(0) scale(1);
        }

        /* ---------- Hero header ---------- */
        .wm-hero {
            position: relative;
            padding: 2.25rem 1.5rem 3.5rem;
            text-align: center;
            color: #fff;
            background: var(--wm-primary);
            overflow: hidden;
        }

        /* Floating bubbles */
        .wm-bubble {
            position: absolute;
            bottom: -40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            animation: wm-float 8s infinite ease-in;
        }
        .wm-bubble:nth-child(1) { left: 8%;  width: 26px; height: 26px; animation-delay: 0s; }
        .wm-bubble:nth-child(2) { left: 25%; width: 14px; height: 14px; animation-delay: 2s; animation-duration: 6s; }
        .wm-bubble:nth-child(3) { left: 50%; width: 34px; height: 34px; animation-delay: 1s; animation-duration: 9s; }
        .wm-bubble:nth-child(4) { left: 72%; width: 18px; height: 18px; animation-delay: 3s; }
        .wm-bubble:nth-child(5) { left: 90%; width: 28px; height: 28px; animation-delay: 4s; animation-duration: 7s; }

        @keyframes wm-float {
            0%   { transform: translateY(0) scale(1);    opacity: 0; }
            15%  { opacity: 1; }
            100% { transform: translateY(-280px) scale(1.3); opacity: 0; }
        }

        .wm-title {
            font-weight: 800;
            font-size: 1.6rem;
            margin: 0;
            animation: wm-fade-up .6s ease both .15s;
        }

        /* ---------- Logo ---------- */
        .wm-logo-wrap {
            position: relative;
            width: 104px;
            height: 104px;
            margin: -3.2rem auto 1rem;
            z-index: 2;
        }
        .wm-logo-wrap::before {
            display: none;
        }
        .wm-logo-inner {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            animation: wm-pop .6s cubic-bezier(.34, 1.56, .64, 1) both .2s;
        }
        .wm-logo-inner img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @keyframes wm-spin { to { transform: rotate(360deg); } }
        @keyframes wm-pop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        @keyframes wm-fade-up {
            from { transform: translateY(14px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        /* ---------- Body ---------- */
        .wm-body {
            padding: 0 2rem 2rem;
            text-align: center;
        }

        .wm-name {
            color: var(--wm-primary);
            font-weight: 800;
            margin-bottom: .5rem;
            animation: wm-fade-up .6s ease both .35s;
        }

        .wm-role {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .85rem;
            border-radius: 50rem;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--wm-primary);
            background: color-mix(in srgb, var(--wm-primary) 12%, transparent);
            margin-bottom: 1rem;
            animation: wm-fade-up .6s ease both .42s;
        }
        .wm-role .wm-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, .6);
            animation: wm-pulse 1.8s infinite;
        }
        @keyframes wm-pulse {
            70%  { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .wm-message {
            color: #5b6784;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            animation: wm-fade-up .6s ease both .5s;
        }

        /* ---------- Button ---------- */
        .wm-btn {
            position: relative;
            overflow: hidden;
            border: 0;
            padding: .75rem 2.5rem;
            border-radius: 50rem;
            font-weight: 700;
            color: #fff;
            background: var(--wm-primary);
            box-shadow: 0 8px 20px color-mix(in srgb, var(--wm-primary) 40%, transparent);
            transition: transform .2s, box-shadow .2s;
            animation: wm-fade-up .6s ease both .58s;
        }
        .wm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px color-mix(in srgb, var(--wm-primary) 50%, transparent);
            color: #fff;
        }
        .wm-btn:active { transform: translateY(0) scale(.97); }
        .wm-btn .wm-arrow { display: inline-block; margin-left: .35rem; transition: transform .25s; }
        .wm-btn:hover .wm-arrow { transform: translateX(5px); }

        /* Ripple */
        .wm-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .5);
            transform: scale(0);
            animation: wm-ripple .6s linear;
            pointer-events: none;
        }
        @keyframes wm-ripple { to { transform: scale(4); opacity: 0; } }

        @media (prefers-reduced-motion: reduce) {
            #welcomeModal *, #welcomeModal *::before { animation: none !important; transition: none !important; }
        }
    </style>

    {{-- ===================== MARKUP ===================== --}}
    <div class="modal fade wm-role-{{ $user->role }}"
         id="welcomeModal"
         data-bs-backdrop="static"
         data-bs-keyboard="false"
         tabindex="-1"
         aria-labelledby="welcomeModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0 shadow">

                {{-- Hero Header --}}
                <div class="wm-hero">

                    <span class="wm-bubble"></span>
                    <span class="wm-bubble"></span>
                    <span class="wm-bubble"></span>
                    <span class="wm-bubble"></span>
                    <span class="wm-bubble"></span>

                    <h5 class="wm-title" id="welcomeModalLabel">
                        Welcome back!
                    </h5>
                </div>

                {{-- Logo (overlaps hero) --}}
                <div class="wm-logo-wrap">
                    <div class="wm-logo-inner">
                        <img src="{{ asset('images/logo.png') }}" alt="CSU Aparri Logo">
                    </div>
                </div>

                {{-- Body --}}
                <div class="wm-body">

                    <h4 class="wm-name">
                        {{ $user->name }}
                    </h4>

                    <div class="wm-role">
                        <span class="wm-dot"></span>
                        {{ ucfirst($user->role) }}
                    </div>

                    <p class="wm-message">
                        {{ $welcomeMessage }}
                    </p>

                    <button type="button"
                            class="wm-btn"
                            id="wmContinueBtn"
                            data-bs-dismiss="modal">
                        Let's Go <span class="wm-arrow">&rarr;</span>
                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- ===================== SCRIPT ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const modalElement = document.getElementById('welcomeModal');

            if (!modalElement) return;

            const welcomeModal = new bootstrap.Modal(modalElement);

            /* ---- Button ripple effect ---- */
            const btn = document.getElementById('wmContinueBtn');
            btn.addEventListener('click', function (e) {
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const ripple = document.createElement('span');
                ripple.className = 'wm-ripple';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top  = (e.clientY - rect.top  - size / 2) + 'px';
                btn.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });

            welcomeModal.show();
        });
    </script>

@endif
