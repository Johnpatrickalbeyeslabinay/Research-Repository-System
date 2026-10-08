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

    <div class="modal fade"
         id="welcomeModal"
         tabindex="-1"
         aria-labelledby="welcomeModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0 rounded-4 shadow">

                {{-- Header --}}
                <div class="modal-header border-0">

                    <h5 class="modal-title fw-bold" id="welcomeModalLabel">
                        Welcome back!
                    </h5>

                </div>

                {{-- Body --}}
                <div class="modal-body text-center px-4 pb-4">

                    <div class="mb-3">
                        <i class="fa-solid fa-fish fa-3x text-primary"></i>
                    </div>

                    <h4 class="fw-bold mb-2">
                        {{ $user->name }}
                    </h4>

                    <p class="text-muted mb-4">
                        {{ $welcomeMessage }}
                    </p>

                    <button type="button"
                            class="btn btn-primary px-4"
                            data-bs-dismiss="modal">
                        Let's Go
                    </button>

                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const modalElement = document.getElementById('welcomeModal');

            if (modalElement) {

                const welcomeModal =
                    new bootstrap.Modal(modalElement);

                welcomeModal.show();
            }

        });
    </script>

@endif
