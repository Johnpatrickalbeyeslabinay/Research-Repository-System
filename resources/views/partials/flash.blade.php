{{--
    Success/error feedback after a form redirects back (approve, reject,
    feedback, user edits, password change). Those controllers always
    flashed a message, but the admin and instructor pages never rendered
    it, so an action gave no sign that it had worked. Shown by
    resources/js/app.js, which auto-hides it after a few seconds.
--}}
@if (session('success') || session('error'))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div class="toast align-items-center border-0 text-white {{ session('error') ? 'bg-danger' : 'bg-success' }}"
            role="status" aria-live="polite" aria-atomic="true" data-flash-toast>
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('error') ?? session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
@endif
