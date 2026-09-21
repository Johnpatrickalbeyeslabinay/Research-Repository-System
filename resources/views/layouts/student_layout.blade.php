<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<style>
    .nav-pills .nav-link {
        color: black;
        background: #fff;
        transition: .2s;
    }

    .nav-pills .nav-link:hover {
        background: #f8f9fa;
    }

    .nav-pills .nav-link.active {
        background: #f3f4f6;
        color: #374151;
    }

.bg-info-soft {
    background-color: #eaf1fe;
  }
  .text-info-strong {
    color: #1e40c9;
  }
  .btn-navy {
    background-color: #1e2a78;
    border-color: #1e2a78;
    color: #fff;
  }
  .btn-navy:hover {
    background-color: #16205c;
    border-color: #16205c;
    color: #fff;
  }
  .btn-toggle-show {
    color: #6c757d;
  }

.toggle-show {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);

    border: none;
    background: transparent;
    padding: 0;
    color: #0d6efd;
    cursor: pointer;
}



.visibility-option {
    cursor: pointer;
    transition: 0.2s ease;
    background-color: transparent;
}

input#public:checked + .visibility-option {
    background-color: #dffbf7;
    border-color: #20c997 !important;
}


.visibility-private {
    cursor: pointer;
    transition: 0.15s ease;
}

input#private:checked + .visibility-private {
    background-color: #f1f1f3;
    box-shadow: 0 0 0 1px black;
}

.update-btn {
    max-width: 200px;
    width: 100%;
    border: 1px solid #e9ecef;
    color: #6c757d;
    transition: all 0.2s ease;
}

.update-btn:hover {
    background-color: #e9ecef !important;
    border-color: black !important;
    color: black !important;
}


/*VISIBILITY BTN*/
input[name="visibility"][value="public"]:checked + .visibility-option {
    background-color: #d1e7dd !important;
    color: black !important;
    border-color: #a3cfbb !important;
}


input[name="visibility"][value="private"]:checked + .visibility-private {
    background-color: #e9ecef !important;
    color: black !important;
    border-color: #e9ecef !important;
}


input[name="visibility"]:checked + label small,
input[name="visibility"]:checked + label i {
    color: black !important;
}


.visibility-option,
.visibility-private {
    cursor: pointer;
    transition: all 0.2s ease;
}


/*favorite icon*/
.favorite-btn {
    color: #6c757d;
    background-color: transparent;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.favorite-btn:hover {
    background-color: #e9ecef;
    color: #495057;
}

.btn-check:checked + .favorite-btn {
    background-color: #adb5bd !important;
    color: #212529 !important;
}

    .stats-scroll {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .stats-scroll::-webkit-scrollbar {
        display: none;
    }

    .stats-scroll > .col-10,
    .stats-scroll > .col-md-5,
    .stats-scroll > .col-lg-3,
    .stats-scroll > .col-xl-3 {
        flex: 0 0 auto;
    }

    @media (min-width: 1200px) {
        .stats-scroll {
            overflow-x: visible !important;
        }

        .stats-scroll > .col-xl-3 {
            flex: 0 0 25%;
        }
    }

    /* Every native <div popover> on this page (Submit/Update Research,
       file-view panels). Two separate problems, both worked around here
       rather than left to browser defaults:
       1. Centering — the UA stylesheet's inset:0 + margin:auto isn't
          reliably applied in practice, which is what left the wizard
          stuck near the top-left instead of centered.
       2. Stacking — an open popover is supposed to render in the
          browser's top layer, above literally everything else,
          regardless of z-index. In practice that promotion isn't taking
          effect here either: the page's own floating chatbot widget
          (z-index: 1050 — see components/chat/widget.blade.php) was
          rendering in front of it, and normal page content behind it was
          winning the stacking order too (the popover has no z-index of
          its own to compete with, since it was relying entirely on top-
          layer promotion). An explicit z-index above every other
          z-indexed element on this page is the fix, same as the explicit
          position above — don't depend on it "just happening". */
    [popover] {
        inset: auto;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        margin: 0;
        z-index: 2000;
    }

    [popover]::backdrop {
        background: rgba(15, 23, 42, .45);
        z-index: 1999;
    }

    /* ==================================================================
       Submit New Research wizard ("research-wizard") — scoped to the
       popover so none of this leaks into the dashboard, view panels, or
       the Update Research form (which keeps its original single-panel
       layout; only its researcher field and dropzone/tray styling are
       shared with the wizard via the same component classes below).
       ================================================================== */
    .research-wizard {
        --rw-accent: #072a89;
        --rw-border: #e5e7eb;
        --rw-muted: #6b7280;
        --rw-text: #111827;
    }

    /* Author-stylesheet rules always win over user-agent ones for the same
       property, REGARDLESS of specificity — so an unconditional
       `.research-wizard { display: flex }` here was permanently
       overriding the browser's own `[popover]:not(:popover-open) {
       display: none }`, which is what keeps a closed popover hidden.
       That's what made the wizard render at all times, even without ever
       clicking "Upload New Research" — the z-index fix just made that
       permanent presence show up on top instead of buried behind other
       content. Gating every box-affecting property behind :popover-open
       means this rule only ever applies once the browser has actually
       shown it, leaving the hidden state entirely up to the browser. */
    .research-wizard:popover-open {
        display: flex;
        flex-direction: column;
        padding: 0;
        background: #fff;
        overflow: hidden; /* clip to the rounded corners; .rw-body scrolls internally instead */
    }

    .research-wizard .rw-header {
        padding: 20px 28px 12px;
    }

    .research-wizard .rw-stepper {
        display: flex;
        align-items: center;
        padding: 0 28px 16px;
    }

    .research-wizard .rw-step {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }

    .research-wizard .rw-step-circle {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12.5px;
        font-weight: 600;
        background: var(--rw-border);
        color: var(--rw-muted);
        flex: 0 0 auto;
        transition: background-color .15s, color .15s;
    }

    .research-wizard .rw-step.is-current .rw-step-circle,
    .research-wizard .rw-step.is-done .rw-step-circle {
        background: var(--rw-accent);
        color: #fff;
    }

    .research-wizard .rw-step-label {
        font-size: 13px;
        color: var(--rw-muted);
        white-space: nowrap;
    }

    .research-wizard .rw-step.is-current .rw-step-label {
        color: var(--rw-accent);
        font-weight: 600;
    }

    .research-wizard .rw-step.is-done .rw-step-label {
        color: var(--rw-text);
    }

    .research-wizard .rw-step-connector {
        flex: 1 1 auto;
        height: 1px;
        background: var(--rw-border);
        margin: 0 8px;
        min-width: 12px;
    }

    .research-wizard .rw-form {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .research-wizard .rw-body {
        padding: 4px 28px 20px;
        overflow-y: auto;
        max-height: calc(90vh - 190px);
    }

    .research-wizard .rw-footer {
        padding: 14px 28px;
        border-top: 1px solid var(--rw-border);
    }

    /* Completed-step summary row — the key scroll-saver: once a step is
       behind the current one, its full field body collapses to one line. */
    .research-wizard .rw-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--rw-border);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 12px;
        background: #f9fafb;
        font-size: 14px;
        color: var(--rw-text);
    }

    .research-wizard .rw-check {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--rw-accent);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex: 0 0 auto;
    }

    /* Label-above field (Title, Abstract — the long/primary fields). */
    .research-wizard .rw-field {
        margin-bottom: 16px;
    }

    .research-wizard .rw-field label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        display: block;
        color: var(--rw-text);
    }

    .research-wizard .rw-field .form-control,
    .research-wizard .rw-field .form-select {
        border: 1px solid var(--rw-border);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
    }

    .research-wizard .rw-field .form-control:focus,
    .research-wizard .rw-field .form-select:focus {
        border-color: var(--rw-accent);
        box-shadow: 0 0 0 3px rgba(7, 42, 137, .12);
    }

    .research-wizard textarea[data-abstract-field] {
        max-height: 140px;
    }

    /* Label-left, value-right row (Type/Year/Adviser — short fields). */
    .research-wizard .rw-row {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--rw-border);
        border-radius: 10px;
        padding: 9px 14px;
        margin-bottom: 12px;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .research-wizard .rw-row:focus-within {
        border-color: var(--rw-accent);
        box-shadow: 0 0 0 3px rgba(7, 42, 137, .12);
    }

    .research-wizard .rw-row-label {
        font-weight: 600;
        font-size: 13px;
        color: var(--rw-text);
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .research-wizard .rw-row select,
    .research-wizard .rw-row input {
        border: 0;
        background: transparent;
        text-align: right;
        font-size: 14px;
        padding: 0;
        flex: 1 1 auto;
        color: var(--rw-text);
        min-width: 0;
    }

    .research-wizard .rw-row select:focus,
    .research-wizard .rw-row input:focus {
        outline: none;
        box-shadow: none;
    }

    /* Compact one-line visibility cards (wizard Review step only — the
       Update Research form keeps the original stacked two-line cards). */
    .research-wizard .rw-visibility {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
    }

    /* Dashed "click to upload" dropzone — shared with the Update Research
       form via the same upload-dropzone component. */
    .research-dropzone {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 28px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s, background-color .15s;
    }

    .research-dropzone:hover {
        border-color: var(--rw-accent, #072a89);
        background: #f5f7ff;
    }

    .research-dropzone.border-primary {
        border-color: var(--rw-accent, #072a89) !important;
        background: #f5f7ff;
    }

    .research-dropzone.border-danger {
        border-color: #dc3545 !important;
    }

    .research-dropzone-icon {
        color: #9ca3af;
    }

    .research-dropzone-link {
        background: none;
        border: 0;
        padding: 0;
        color: var(--rw-accent, #072a89);
        text-decoration: underline;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    }

    .rw-abstract-preview {
        border: 1px dashed var(--rw-border, #e5e7eb);
        border-radius: 10px;
        padding: 10px 12px;
        margin-top: 8px;
        background: #fafafa;
    }

    /* AI/extracted suggestion trays — keywords and SDGs, shared between
       the wizard and the Update Research form. Nothing here is selected
       until the student clicks it (see research-submission.js). */
    .keyword-suggestions,
    .sdg-suggestions {
        border: 1px dashed var(--rw-border, #e5e7eb);
        border-radius: 10px;
        padding: 10px 12px;
        background: #fafafa;
    }

    .keyword-suggestion-chip {
        border: 1px dashed var(--rw-accent, #072a89);
        background: #eef2ff;
        border-radius: 999px;
        font-size: 12.5px;
        padding: 4px 10px;
        color: var(--rw-accent, #072a89);
    }

    .keyword-suggestions-actions {
        font-size: 12px;
    }

    .sdg-suggestion-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        border: 1px solid var(--rw-border, #e5e7eb);
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 13px;
        background: #fff;
    }

    /* Researcher list rows (researcher-input component). */
    .researcher-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--rw-border, #e5e7eb);
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 6px;
        background: #fff;
    }

    .researcher-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #eef2ff;
        color: var(--rw-accent, #072a89);
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
</style>


<body class="text-dark" style="font-family: sans-serif, arial;
  font-size: var(--font-size-sm);            /* 14px base */
  color: var(--color-text-primary);          /* #111827 default text */
  background: #f9fafb;
  margin: 0;
  padding: 0;
  line-height: 1.5;
}">




    <button class="navbar-toggler border-0 me-2 d-lg-none" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#sidebar">
        <span class="navbar-toggler-icon"></span>
    </button>


    <!--sidebar-->

    <div class="container-fluid">
        <div class="row">

           <nav class="navbar navbar-light bg-white border-bottom d-lg-none">
                <div class="container-fluid">

                    <!-- Logo + Program -->
                    <div class="d-flex align-items-center">

                    <img src="{{ asset('images/logo.png') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:90px;height:90px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">CSU APARRI</h5>
                        <small class="text-secondary">Fisheries Program</small>
                    </div>

                </div>

                    <!-- Bell + Hamburger -->
                    <div class="d-flex align-items-center">

                        <!-- Notification -->
                        <a href="{{ route('notifications.index') }}" class="btn border-0 p-1 me-2 position-relative">
                            <i class="fa-regular fa-bell"></i>
                            @if(($unreadNotificationsCount ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.55rem;">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>

                        <!-- Hamburger -->
                        <button class="navbar-toggler border-0 p-1" type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#sidebar">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                    </div>

                </div>
            </nav>


            <aside class="col-lg-2 d-none d-lg-flex flex-column bg-white border-end p-0 position-fixed"
                style="height:100vh; width:250px; left:0; top:0;">

                <!-- Header -->
                <div class="offcanvas-header py-4 px-3 text-dark bg-white">


                    <div class="d-flex align-items-center">

                    <img src="{{ asset('images/logo.png') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:70px;height:70px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">CSU APARRI</h5>
                        <small class="text-secondary">Fisheries Program</small>
                    </div>

                </div>
                </div>


                <div class="border-top p-2 d-flex align-items-center gap-2" style=" background-color: #EFF6FF">

                    <!-- ICON WITH CIRCLE BACKGROUND -->
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                        style="width: 40px; height: 35px; font-size: 18px; background: #072a89;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>

                    <!-- USER INFO -->
                    <div>
                        <h6 class="fw-bold mb-0">
                            {{ Auth::user()->name }}
                        </h6>
                        <small class="text-secondary">
                            {{ Auth::user()->role }}
                        </small>
                    </div>

                </div>

                <!-- Menu -->
                <div class="flex-grow-1 p-3">

                    <ul class="nav flex-column">

                        <li class="nav-item">

                            <a class="nav-link {{ $view == 'dashboard' ? 'active text-white bg-primary rounded' : 'text-dark bg-light rounded' }}"
                            href="{{ url('/student?view=dashboard') }}">

                                <i class="fa-solid fa-book me-2"></i>

                                Dashboard

                            </a>

                        </li>

                        <li class="nav-item mt-2">

                            <a class="nav-link active bg-primary rounded text-dark bg-light"
                                href="{{ route('repository')  }}">

                                <i class="fa-solid fa-folder me-2"></i>

                                Repository

                            </a>

                        </li>

                    </ul>

                </div>

                <!-- Footer -->
                <div class="border-top p-3">


                <button type="button"
                        class="rounded-2 w-100 bg-white border-0 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#manageaccount">

                    <i class="fa-solid fa-gear fa-sm me-3"></i>
                    Manage Account
                </button>

                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf

                        <button class="btn btn-outline-danger w-100 text-start">

                            <i class="fa-solid fa-right-from-bracket me-2"></i>

                            Logout

                        </button>

                    </form>

                </div>

            </aside>

        </div>
    </div>



    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebar">

        <div class="offcanvas-header text-white">


            <div class="offcanvas-header py-4 px-3 text-dark bg-white">


               <div class="d-flex align-items-center">

                    <img src="{{ asset('images/logo.png') }}"
                        alt="CSU Aparri Logo"
                        class="me-3 rounded-circle"
                        style="width:90px;height:90px;object-fit:cover;">

                    <div class="d-flex flex-column">
                        <h5 class="fw-bold mb-0">CSU APARRI</h5>
                        <small class="text-secondary">Fisheries Program</small>
                    </div>

                </div>

            </div>



            <button class="btn-close btn-close-dark" data-bs-dismiss="offcanvas"></button>

        </div>

        <div class="border-top p-2 d-flex align-items-center gap-2" style=" background-color: #EFF6FF">

            <!-- ICON WITH CIRCLE BACKGROUND -->
            <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                style="width: 45px; height: 45px; font-size: 18px; background: #072a89">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <!-- USER INFO -->
            <div>
                <h6 class="fw-bold mb-0">
                    {{ Auth::user()->name}}
                </h6>
                <small class="text-secondary">
                    {{ Auth::user()->role}}
                </small>
            </div>

        </div>

        <div class="offcanvas-body">

            <ul class="nav flex-column">

                <li class="nav-item">

                    <a class="nav-link {{ request()->query('view') === 'subjects' ? 'active text-white bg-primary rounded' : 'text-dark' }}"
                        href="{{ url('/student_dashboard?view=subjects') }}">

                        <i class="fa-solid fa-book me-2"></i>

                        Dashboard

                    </a>

                </li>

                <li class="nav-item mt-2">

                     <a class="nav-link active bg-primary rounded text-dark bg-light"
                                href="{{ route('repository')  }}">

                                <i class="fa-solid fa-folder me-2"></i>

                                Repository

                            </a>

                </li>
            </ul>

            <hr>



        </div>

        <!-- Footer -->
        <div class="border-top p-3">


        <button type="button"
                        class="rounded-2 w-100 bg-white border-0 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#manageaccount">

                    <i class="fa-solid fa-gear fa-sm me-3"></i>
                    Manage Account
                </button>

          <form action="{{ route('logout') }}" method="POST" class="mt-3">
            @csrf

            <button class="btn btn-outline-danger w-100 text-start">

              <i class="fa-solid fa-right-from-bracket me-2"></i>

              Logout

            </button>

          </form>

        </div>

    </div>

    <div class="container-fluid">
        <div class="row">

            <aside class="col-lg-2 d-none d-lg-flex flex-column">
                ...
            </aside>

            <main class="col-12 col-lg-10 p-4">
                @yield('student')
            </main>

        </div>
    </div>







<x-chat.widget />

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/js/research-submission.js', 'resources/js/chat.js'])

  <script>
    lucide.createIcons();
  </script>
</body>

</html>
