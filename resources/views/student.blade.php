@extends('layouts.student_layout')

@section('title', 'Student Dashboard')

@section('student')




<!--NOTIFICATION ALERT-->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"
        role="alert"
        id="success-alert">

        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}

    </div>

    <script>
        setTimeout(function () {
            const alert = document.getElementById('success-alert');

            if (alert) {
                alert.classList.remove('show');

                setTimeout(function () {
                    alert.remove();
                }, 150);
            }
        }, 3000);
    </script>
@endif









<!--====================DASHBOARD=====================-->
@if($view == 'dashboard')

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                My Research Dashboard
            </h3>

            <p class="text-secondary mb-0">
                Manage your research submissions and track progress
            </p>
        </div>

        <div class="mt-3 mt-md-0">


            <button class="rounded-2 px-2 py-1 text-white border-0" style="background: #072a89;" popovertarget="popover">
                <i class=" fa-solid fa-upload me-2"></i>
                Upload New Research
            </button>
        </div>
    </div>

    <div class="container-fluid py-4">

        <div class="row g-4">

        <!--MANAGE ACCOUNT BTN-->
        <div class="modal fade" id="manageaccount" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold">Manage Account</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="bg-info-soft rounded-3 p-3 mb-4">
                <p class="mb-1">
                    <strong>Name:</strong> {{ Auth::user()?->name }}
                </p>

                <p class="mb-1">
                    <strong>User ID:</strong> {{ Auth::user()->user_id }}
                </p>

                <p class="mb-0">
                    <strong>Role:</strong> {{ Auth::user()->role }}
                </p>
            </div>

            <h6 class="fw-bold mb-3">Change Password</h6>

            <form action="{{ route('account.password.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Current Password
                    </label>

                    <input type="password"
                           name="current_password"
                           class="form-control @error('current_password') is-invalid @enderror"
                           placeholder="Enter current password">

                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        New Password
                    </label>

                    <input type="password"
                           name="new_password"
                           class="form-control @error('new_password') is-invalid @enderror"
                           placeholder="At least 8 characters">

                    @error('new_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">
                        Confirm New Password
                    </label>

                    <input type="password"
                           name="new_password_confirmation"
                           class="form-control"
                           placeholder="Repeat new password">
                </div>

                <div class="d-flex gap-3">

                    <button type="submit"
                            class="text-white rounded-3 border-0 flex-grow-1 fw-bold py-2"
                            style="background:#072a89;">
                        Save New Password
                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>











<!--======================================================================================================================-->
        <!--Uploadfiles-->
            @php
                // Which step to land on when this popover reopens after a
                // failed submission — the first step containing a field the
                // server rejected, not always step 1. Gated the same way as
                // the error alert below (errors are a single session-wide
                // bag shared with the Update Research forms).
                $wizardStepFields = [
                    1 => ['title', 'research_type', 'publication_year', 'advisers'],
                    2 => ['researchers'],
                    3 => ['file', 'desc', 'keywords', 'keyword_sources', 'sdg'],
                    4 => [''],
                ];
                $createErrorStep = null;
                if ($errors->any() && old('_popover_target', 'popover') === 'popover') {
                    foreach ($errors->keys() as $errorKey) {
                        foreach ($wizardStepFields as $step => $fields) {
                            foreach ($fields as $field) {
                                if ($errorKey === $field || str_starts_with($errorKey, $field.'.')) {
                                    $createErrorStep = $createErrorStep === null ? $step : min($createErrorStep, $step);
                                }
                            }
                        }
                    }
                }
                $createErrorStep = $createErrorStep ?? 1;
            @endphp

            <div id="popover" popover="manual" class="research-wizard rounded-4 border-0 shadow"
                data-wizard data-wizard-error-step="{{ $createErrorStep }}"
                style="width: 720px; max-width: 95vw; max-height: 90vh;">

                <div class="rw-header d-flex justify-content-between align-items-center">
                    <h3 class="fw-bold mb-0">Submit New Research</h3>

                    <button class="btn-close" popovertarget="popover" popovertargetaction="hide"></button>
                </div>

                {{-- Errors are shared across every form on the page (one session error
                     bag), so this is gated by _popover_target below — otherwise a
                     failed Update Research submission would also render its errors
                     here, in a popover the user never opened. --}}
                @if ($errors->any() && old('_popover_target', 'popover') === 'popover')
                    <div class="alert alert-danger mx-4 mt-3 mb-0">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <nav class="rw-stepper" data-wizard-steps>
                    <div class="rw-step" data-step-indicator="1">
                        <div class="rw-step-circle">1</div>
                        <div class="rw-step-label">Details</div>
                    </div>
                    <div class="rw-step-connector"></div>
                    <div class="rw-step" data-step-indicator="2">
                        <div class="rw-step-circle">2</div>
                        <div class="rw-step-label">Researchers</div>
                    </div>
                    <div class="rw-step-connector"></div>
                    <div class="rw-step" data-step-indicator="3">
                        <div class="rw-step-circle">3</div>
                        <div class="rw-step-label">Document</div>
                    </div>
                    <div class="rw-step-connector"></div>
                    <div class="rw-step" data-step-indicator="4">
                        <div class="rw-step-circle">4</div>
                        <div class="rw-step-label">Review</div>
                    </div>
                </nav>

                <form action="{{ route('student.store') }}" data-turbo="false" method="POST" enctype="multipart/form-data" class="rw-form">

                    @csrf
                    <input type="hidden" name="_popover_target" value="popover">

                    <div class="rw-body">

                        <section class="rw-step-panel" data-wizard-step="1">
                            <div class="rw-summary-row" data-step-summary="1" hidden>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rw-check"><i class="bi bi-check-lg"></i></span>
                                    <span data-step-summary-text>—</span>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0" data-step-edit="1">Edit</button>
                            </div>

                            <div data-step-body="1">
                                <div class="rw-field">
                                    <label>Research Title</label>
                                    <input type="text" name="title" class="form-control" required
                                        value="{{ old('title') }}"
                                        placeholder="Enter the title of your research">
                                </div>

                                <div class="row g-2">

                                    <!--<div class="col-6">
                                        <div class="rw-row">
                                            <span class="rw-row-label">Type</span>
                                            <input type="text" name="type" value="Thesis">
                                        </div>
                                    </div>-->



                                    <!--<div class="rw-row">
                                        <span class="rw-row-label">Adviser <span class="text-danger">*</span></span>
                                        <select name="adviser_id" class="form-select form-select-sm" required>
                                            <option value="">Select adviser</option>
                                            @foreach($instructors as $instructor)
                                                <option value="{{ $instructor->id }}" {{ (int) old('adviser_id') === $instructor->id ? 'selected' : '' }}>
                                                    {{ $instructor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>-->


                            <div class="mb-3">
                                <small class="fw-semibold">Thesis Adviser</small>
                                <div class="input-group input-group-sm" style="max-width: 100%;">

                                    <select
                                        name="advisers[]"
                                        class="form-select"
                                        multiple
                                        size="1"
                                        required>

                                        @foreach ($instructors as $instructor)
                                            <option value="{{ $instructor->id }}">
                                                {{ $instructor->name }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            </div>



                                    <div class="col-6">
                                      <div class="rw-row">
                                            <span class="rw-row-label">Year</span>
                                            <select name="publication_year" class="form-select form-select-sm">
                                                @php $currentYear = (int) date('Y'); @endphp
                                                @for ($year = $currentYear; $year >= $currentYear - 10; $year--)
                                                    <option value="{{ $year }}" {{ (int) old('publication_year', $currentYear) === $year ? 'selected' : '' }}>{{ $year }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </section>

                        <section class="rw-step-panel" data-wizard-step="2">
                            <div class="rw-summary-row" data-step-summary="2" hidden>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rw-check"><i class="bi bi-check-lg"></i></span>
                                    <span data-step-summary-text>—</span>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0" data-step-edit="2">Edit</button>
                            </div>

                            <div data-step-body="2">
                                <x-research.researcher-input prefix="create" :otherStudents="$otherStudents" />
                            </div>
                        </section>

                        <section class="rw-step-panel" data-wizard-step="3">
                            <div class="rw-summary-row" data-step-summary="3" hidden>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rw-check"><i class="bi bi-check-lg"></i></span>
                                    <span data-step-summary-text>—</span>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0" data-step-edit="3">Edit</button>
                            </div>

                            <div data-step-body="3">
                                <x-research.upload-dropzone prefix="create" />

                                <div class="rw-field">
                                    <label>Abstract <span class="text-danger">*</span></label>

                                    <textarea class="form-control" name="desc" rows="3" required data-abstract-field
                                        placeholder="Provide a comprehensive abstract">{{ old('desc') }}</textarea>

                                    <small class="d-none" data-abstract-badge></small>

                                    <div class="rw-abstract-preview d-none" data-abstract-preview>
                                        <p class="small text-muted mb-1">We found this text in your PDF — is this your abstract?</p>
                                        <p class="small fw-semibold mb-2" data-abstract-preview-text></p>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-abstract-preview-use>Use this</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-abstract-preview-dismiss>Write my own</button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-analyze-btn="create" data-analyze-url="{{ route('research.analyze') }}" disabled>
                                        <i class="bi bi-stars me-1"></i>
                                        Suggest keywords &amp; SDGs from my abstract
                                    </button>

                                    <div data-analyze-status class="small mt-2"></div>

                                    <div data-analyze-stale class="alert alert-warning py-2 px-3 mt-2 mb-0 d-none">
                                        Research content changed.
                                        <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-analyze-btn="create" data-analyze-url="{{ route('research.analyze') }}">
                                            Regenerate AI suggestions?
                                        </button>
                                    </div>

                                    <input type="hidden" name="ai_analysis_fingerprint" data-ai-fingerprint-field value="">
                                    <input type="hidden" name="ai_model_used" data-ai-model-field value="">
                                    <input type="hidden" data-fallback-excerpt-field value="">
                                </div>

                                <x-research.keyword-input prefix="create" />

                                <x-research.sdg-selector prefix="create" :sdgs="$sdgs" />
                            </div>
                        </section>

                        <section class="rw-step-panel" data-wizard-step="4">
                        <div data-step-body="4">

                            <div class="mb-3">

                                <label class="form-label fw-semibold">
                                    Research Visibility
                                </label>

                                <div class="alert alert-info rounded-3 mb-0">

                                    <div class="d-flex align-items-start">

                                        <i class="fa-solid fa-lock me-2 mt-1"></i>

                                        <div>
                                            <strong>Private during review</strong>
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <x-research.review-summary prefix="create" :expanded="true" />

                        </div>
                    </section>

                    </div>

                    <div class="rw-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary" data-wizard-back hidden>
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>

                        <div class="d-flex gap-2 ms-auto">
                            <button type="button" class="btn btn-outline-secondary" popovertarget="popover"
                                popovertargetaction="hide">
                                Cancel
                            </button>

                            <button type="button" class="rounded-2 px-3 py-2 text-white border-0" style="background: var(--rw-accent);" data-wizard-next>
                                Next <i class="bi bi-arrow-right ms-1"></i>
                            </button>

                            <button type="submit" class="rounded-2 px-3 py-2 text-white border-0 d-none" style="background: var(--rw-accent);" data-wizard-submit data-submit-btn>
                                Submit Research
                            </button>
                        </div>
                    </div>

                </form>

            </div>



                        <!-- Dashboard Statistics -->
                <div class="row flex-nowrap overflow-auto g-3 pb-2 stats-scroll">

                    <!-- Total Submissions -->
                    <div class="col-10 col-md-5 col-lg-3 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">

                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-file-lines text-primary fs-4"></i>
                                </div>

                                <div>
                                    <p class="text-muted mb-1 small">
                                        Total Submissions
                                    </p>

                                    <h3 class="fw-bold mb-0">
                                        {{ $totalUploads }}
                                    </h3>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- Published -->
                    <div class="col-10 col-md-5 col-lg-3 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">

                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-circle-check text-success fs-4"></i>
                                </div>

                                <div>
                                    <p class="text-muted mb-1 small">
                                        Published
                                    </p>

                                    <h3 class="fw-bold mb-0">
                                        {{ $approvedUploads }}
                                    </h3>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- Pending -->
                    <div class="col-10 col-md-5 col-lg-3 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">

                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-clock text-warning fs-4"></i>
                                </div>

                                <div>
                                    <p class="text-muted mb-1 small">
                                        Pending
                                    </p>

                                    <h3 class="fw-bold mb-0">
                                        {{ $pendingUploads }}
                                    </h3>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- Rejected -->
                    <div class="col-10 col-md-5 col-lg-3 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center">

                                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-circle-xmark text-danger fs-4"></i>
                                </div>

                                <div>
                                    <p class="text-muted mb-1 small">
                                        Rejected
                                    </p>

                                    <h3 class="fw-bold mb-0">
                                        {{ $rejectedUploads }}
                                    </h3>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>


        </div>

    </div>

    <!-- NAV BUTTONS -->
    <div class="bg-white rounded-5 shadow-sm d-inline-flex gap-2 nav nav-pills" id="dashboardTab" role="tablist">

        <!-- My Submissions -->
        <button class="nav-link active rounded-5 px-2 py-1" id="submission-tab" data-bs-toggle="pill"
            data-bs-target="#submission" type="button" role="tab">
            My Submissions
        </button>

        <!-- Review Request -->
        <button class="nav-link rounded-5 px-2 py-1" id="request-tab" data-bs-toggle="pill"
            data-bs-target="#request" type="button" role="tab">
            Review Request
        </button>

        <!-- Notifications -->
        <button class="nav-link rounded-5 px-2 py-1  d-flex align-items-center gap-2" id="notification-tab"
            data-bs-toggle="pill" data-bs-target="#notification" type="button" role="tab">
            Notifications
            <!--<span class="badge d-flex align-items-center justify-content-center text-danger">
                <i class="fa-solid fa-comment-dots"></i>
            </span>-->
        </button>

        <!-- Bookmarks -->
        <!--<button class="nav-link rounded-5 px-2 py-1" id="bookmark-tab" data-bs-toggle="pill" data-bs-target="#bookmark"
            type="button" role="tab">
            Bookmarks
        </button>-->

    </div>


    <div class="tab-content">

        <!-- ================= SUBMISSIONS ================= -->
        <div class="tab-pane fade show active" id="submission">

            <div class="row g-3 mt-3">

                @foreach($studFiles as $file)

                    <!-- CARD -->
                    <div class="col-12">

                        <div class="card border-1 shadow-sm rounded-4">

                            <div class="card-body">

                                <div class="row align-items-center">

                                    <div class="col-lg-9">

                                        <h5 class="fw-bold mb-2">
                                            {{ $file->title }}
                                        </h5>

                                        <!-- STATUS & VISIBILITY - MOBILE -->
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-3 d-lg-none">

                                            <!-- STATUS -->
                                            @if(empty($file->status) || $file->status == 'pending')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #fff3cd;">
                                                    Pending
                                                </span>

                                            @elseif($file->status == 'approved')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #d1e7dd;">
                                                    Approved
                                                </span>

                                            @elseif($file->status == 'rejected')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #f8d7da;">
                                                    Rejected
                                                </span>
                                            @endif


                                            <!-- VISIBILITY -->
                                            @if(empty($file->visibility) || $file->visibility == 'private')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #f8f9fa;">
                                                    <i class="fa-solid fa-lock fa-2xs"></i>
                                                    Private
                                                </span>

                                            @elseif($file->visibility == 'public')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #edf7ef;">
                                                    <i class="fa-solid fa-globe fa-2xs"></i>
                                                    Public
                                                </span>
                                            @endif

                                            {{-- store()/update() always persist visibility='private' first and
                                                 keep the student's real choice in requested_visibility — public
                                                 only takes effect once an admin approves it. Without this, a
                                                 student who asked for Public just sees "Private" with nothing
                                                 explaining why. --}}
                                            @if($file->requested_visibility === 'public' && $file->visibility === 'private')
                                                <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #fff3cd;">
                                                    <i class="fa-solid fa-globe fa-2xs"></i>
                                                    Public requested — pending approval
                                                </span>
                                            @endif

                                        </div>


                                        <!-- STATUS & VISIBILITY - DESKTOP -->
                                        <div class="position-absolute top-0 end-0 m-3 d-flex align-items-center gap-2 d-none d-lg-flex">

                                            <!-- STATUS -->
                                            @if(empty($file->status) || $file->status == 'pending')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #fff3cd;">
                                                    Pending
                                                </span>

                                            @elseif($file->status == 'approved')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #d1e7dd;">
                                                    Approved
                                                </span>

                                            @elseif($file->status == 'rejected')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #f8d7da;">
                                                    Rejected
                                                </span>
                                            @endif


                                            <!-- VISIBILITY -->
                                            @if(empty($file->visibility) || $file->visibility == 'private')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #f8f9fa;">
                                                    <i class="fa-solid fa-lock fa-2xs"></i>
                                                    Private
                                                </span>

                                            @elseif($file->visibility == 'public')
                                                <span class="badge text-dark rounded-pill px-2 py-1"
                                                    style="background-color: #edf7ef;">
                                                    <i class="fa-solid fa-globe fa-2xs"></i>
                                                    Public
                                                </span>
                                            @endif

                                            @if($file->requested_visibility === 'public' && $file->visibility === 'private')
                                                <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #fff3cd;">
                                                    <i class="fa-solid fa-globe fa-2xs"></i>
                                                    Public requested
                                                </span>
                                            @endif

                                        </div>


                                        <!-- RESEARCH INFO -->
                                        <div class="d-flex flex-wrap gap-3 text-muted small mb-3">

                                            <span>
                                                <i class="bi bi-person-lines-fill me-1"></i>
                                                Adviser:
                                                {{ $file->adviser }}
                                            </span>

                                            <span>
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ $file->created_at->format('M d, Y') }}
                                            </span>

                                            <span>
                                                <i class="bi bi-eye me-1"></i>
                                                {{ $file->views ?? 0 }}
                                            </span>

                                            <span>
                                                <i class="bi bi-download me-1"></i>
                                                {{ $file->downloads ?? 0 }}
                                            </span>

                                        </div>

                                        {{-- Researchers/type/publication-year/curated-keywords/SDGs — collected
                                             at submission, previously shown nowhere on this card at all (only
                                             the auto-generated title-word tags below stood in for "keywords"). --}}
                                        <x-research.meta :file="$file" compact
                                            :show="['researchers', 'type', 'year',]" />
                                        <!-- 'keywords', 'sdgs'-->

                                        <!--abstract-->
                                        <p class="text-secondary mb-3">
                                            {{ \Illuminate\Support\Str::limit($file->desc, 180) }}
                                        </p>


                                        <!--Rejected resaon-->
                                        @if($file->status === 'rejected' && ($file->rejection_reason || $file->admin_reject))
                                                <div class="d-flex align-items-start">

                                                    <i class="fa-solid fa-circle-exclamation me-2 mt-1" style="color: #e57373;"></i>

                                                    <div>

                                                        <div class="fw-semibold mb-1" style="color: #e57373;">
                                                            @if(!empty($file->admin_reject))
                                                                <small>Admin rejected your paper</small>
                                                            @else
                                                                <small>{{ $file->adviser }} rejected your paper</small>
                                                            @endif
                                                        </div>

                                                        <!--<div class="small">
                                                            {{ $file->admin_reject ?? $file->rejection_reason }}
                                                        </div>-->

                                                    </div>

                                            </div>

                                        @endif

                                        <!--@if($file->instructor_feedback)
                                            <div class="rounded-3 p-2 mt-2" style="background-color:#FFF8E1;">
                                                <small class="fw-semibold text-secondary d-block mb-1">
                                                    <i class="bi bi-chat-left-text me-1"></i>
                                                    Adviser Feedback
                                                </small>
                                                <small>{{ $file->instructor_feedback }}</small>
                                            </div>
                                        @endif-->

                                    </div>

                                </div>

                                <button class="view rounded px-2 py-1 mt-3 bg-white"
                                    style="max-width:200px;width:100%; border: 1px solid #00008b; color: #00008b;"
                                    popovertarget="popover-{{ $file->id }}">

                                    <i class="bi bi-eye me-2"></i>
                                    View in Repository
                                </button>

                                @if($file->status == 'rejected')
                                    <button class="view rounded px-2 py-1 mt-3 bg-white update-btn"
                                        popovertarget="update-sub-{{ $file->id }}">
                                        <i class="fa-regular fa-pen-to-square me-2"></i>
                                        Update Submission
                                    </button>
                                @endif

                            </div>

                        </div>

                    </div>




                    <!--POPOVER-UPDATE-->
                    @php $upfx = 'update-'.$file->id; @endphp
                    @php $limitReached = ! $file->canResubmit(); @endphp
                    <div id="update-sub-{{ $file->id }}" popover="manual" class="rounded-4 border-0 shadow p-4"
                        style="width: 560px; max-width: 95vw; max-height: 90vh; overflow-y: auto;">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold mb-0">Update Research</h3>

                            <button class="btn-close" popovertarget="update-sub-{{ $file->id }}"
                                popovertargetaction="hide">
                            </button>
                        </div>

                        @if ($limitReached)
                            <div class="alert alert-danger py-2 px-3">
                                <i class="bi bi-lock-fill me-1"></i>
                                You've used all {{ $file::MAX_RESUBMITS }} resubmissions. This research can no longer be updated.
                                Please contact your adviser or an admin to request an extension.
                            </div>
                        @else
                            <div class="alert alert-info py-2 px-3 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Resubmissions left: <strong>{{ $file->resubmitsLeft() }}</strong> of {{ $file::MAX_RESUBMITS }}
                            </div>
                        @endif

                        @if ($errors->any() && old('_popover_target') === 'update-sub-'.$file->id)
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form data-turbo="false" action="{{ route('student.update', $file->id) }}" method="POST" enctype="multipart/form-data">

                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_popover_target" value="update-sub-{{ $file->id }}">

                            <!-- Research Title -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Research Title <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="title"
                                    class="form-control form-control-lg"
                                    value="{{ old('title', $file->title) }}"
                                    placeholder="Enter the title of your research"
                                    required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Research Type</label>

                                    <select name="research_type" class="form-select">
                                        <option value="">Select type</option>
                                        @foreach(['Thesis', 'Capstone', 'Case Study', 'Other'] as $type)
                                            <option value="{{ $type }}" {{ old('research_type', $file->research_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Publication Year</label>

                                    <select name="publication_year" class="form-select">
                                        @php $currentYear = (int) date('Y'); @endphp
                                        @for ($year = $currentYear; $year >= $currentYear - 10; $year--)
                                            <option value="{{ $year }}" {{ (int) old('publication_year', $file->publication_year ?? $currentYear) === $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <x-research.researcher-input :prefix="$upfx" :otherStudents="$otherStudents"
                                :seed="$file->authors->map(fn ($a) => ['name' => $a->author_name, 'user_id' => $a->user_id])" />

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Research Adviser <span class="text-danger">*</span>
                                </label>

                                <select name="adviser_id" class="form-select" required>
                                    <option value="">Select adviser</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ (int) old('adviser_id', $file->adviser_id) === $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="my-4">

                            <x-research.upload-dropzone :prefix="$upfx" :required="false" />

                            <small class="text-secondary d-block mt-n2 mb-3">
                                Current file: {{ basename($file->file_path) }} — leave blank to keep it.
                            </small>

                            <!-- Abstract -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Abstract <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    class="form-control"
                                    name="desc"
                                    rows="4"
                                    placeholder="Provide a comprehensive abstract"
                                    required
                                    data-abstract-field>{{ old('desc', $file->desc) }}</textarea>

                                <small class="d-none" data-abstract-badge></small>
                            </div>

                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-analyze-btn="{{ $upfx }}" data-analyze-url="{{ route('research.analyze') }}">
                                    <i class="bi bi-stars me-1"></i>
                                    Analyze Research
                                </button>

                                <div data-analyze-status class="small mt-2"></div>

                                <div data-analyze-stale class="alert alert-warning py-2 px-3 mt-2 mb-0 d-none">
                                    Research content changed.
                                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-analyze-btn="{{ $upfx }}" data-analyze-url="{{ route('research.analyze') }}">
                                        Regenerate AI suggestions?
                                    </button>
                                </div>

                                <input type="hidden" name="ai_analysis_fingerprint" data-ai-fingerprint-field value="">
                                <input type="hidden" name="ai_model_used" data-ai-model-field value="">
                            </div>

                            <x-research.keyword-input :prefix="$upfx" :seed="$file->keywords->pluck('display_name')->all()" />

                            <x-research.sdg-selector :prefix="$upfx" :sdgs="$sdgs" :selected="$file->sdgs->pluck('id')->all()" />

                            <x-research.review-summary :prefix="$upfx" />

                            <!-- Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button
                                    type="submit"
                                    class="rounded-2 p-2 text-white border-0"
                                    data-submit-btn
                                    @disabled($limitReached)
                                    style="
                                        background: {{ $limitReached ? '#6c757d' : '#072a89' }};
                                        cursor: {{ $limitReached ? 'not-allowed' : 'pointer' }};
                                        opacity: {{ $limitReached ? '0.75' : '1' }};
                                    "
                                >
                                    Submit Research
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    popovertarget="update-sub-{{ $file->id }}"
                                    popovertargetaction="hide">
                                    Cancel
                                </button>
                            </div>

                        </form>
                    </div>






                     <!-- POPOVER FILE -->
                    <div id="popover-{{ $file->id }}" popover
                        style="border: 1px solid #dee2e6;  max-width: 1200px; width: 90%; overflow-y: auto; max-height: 90vh;">
                        <div class="container py-5">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-4 p-md-5">
                                    <h5 class="fw-bold mb-2"> {{ $file->title }} </h5>

                                    {{-- Was: an unclosed <span> (views never actually shown, only
                                         downloads), "Author" = $file->name only (hid every co-author),
                                         "Year Published" = the upload date (not publication_year), and
                                         "Keywords" = a count of auto-generated title words rather than
                                         the curated keyword list. meta.blade.php fixes all four. --}}
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                                        @if($file->status == 'approved')
                                            <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #d1e7dd;">Approved</span>
                                        @elseif($file->status == 'rejected')
                                            <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #f8d7da;">Rejected</span>
                                        @else
                                            <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #fff3cd;">Pending</span>
                                        @endif

                                        @if($file->visibility === 'public')
                                            <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #edf7ef;"><i class="fa-solid fa-globe fa-2xs"></i> Public</span>
                                        @else
                                            <span class="badge text-dark rounded-pill px-2 py-1" style="background-color: #f8f9fa;"><i class="fa-solid fa-lock fa-2xs"></i> Private</span>
                                        @endif
                                    </div>

                                    <x-research.meta :file="$file" />

                                    <div class="mb-4">
                                        <span class="text-muted">Submitted:</span>
                                        {{ $file->created_at->format('M j, Y') }}
                                    </div>

                                    <h6 class="fw-bold">Abstract</h6>
                                    <p class="text-muted" style="text-align: justify;">
                                        {{ $file->desc ?? 'No abstract available.' }}
                                    </p>

                                    <!--@if($file->instructor_feedback)
                                        <div class="rounded-3 p-3 mt-3" style="background-color:#FFF8E1;">
                                            <small class="fw-semibold text-secondary d-block mb-1">
                                                <i class="bi bi-chat-left-text me-1"></i>
                                                Adviser Feedback
                                            </small>
                                            {{ $file->instructor_feedback }}
                                        </div>
                                    @endif-->





                                    {{-- Instructor Feedback History --}}
                                        <div class="mt-4">

                                            <h5 class="fw-bold mb-3">
                                                <i class="bi bi-chat-left-text me-2"></i>
                                                Instructor Feedback
                                            </h5>

                                            @php
                                                $feedbackHistory = [];

                                                if (!empty($file->instructor_feedback)) {
                                                    $decodedFeedback = json_decode($file->instructor_feedback, true);

                                                    if (is_array($decodedFeedback)) {
                                                        $feedbackHistory = $decodedFeedback;
                                                    } else {
                                                        // Support old single feedback
                                                        $feedbackHistory[] = [
                                                            'user_id' => $file->adviser_id,
                                                            'feedback' => $file->instructor_feedback,
                                                            'created_at' => $file->updated_at?->toDateTimeString(),
                                                        ];
                                                    }
                                                }
                                            @endphp

                                            @forelse($feedbackHistory as $feedback)

                                                @php
                                                    $commenter = \App\Models\User::find($feedback['user_id'] ?? null);
                                                @endphp

                                                <div class="border rounded-3 p-3 mb-3">

                                                    <div class="d-flex justify-content-between align-items-start">

                                                        <div>
                                                            <strong>
                                                                {{ $commenter->name ?? 'Unknown Instructor' }}
                                                            </strong>

                                                            <div class="text-muted small">
                                                                @if(!empty($feedback['created_at']))
                                                                    {{ \Carbon\Carbon::parse($feedback['created_at'])->format('M j, Y h:i A') }}
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <i class="bi bi-chat-left-text text-primary"></i>

                                                    </div>

                                                    <div class="mt-3">
                                                        {{ $feedback['feedback'] ?? '' }}
                                                    </div>

                                                </div>

                                            @empty

                                                <div class="alert alert-secondary">
                                                    <i class="bi bi-chat-left-text me-2"></i>
                                                    No feedback has been posted yet.
                                                </div>

                                            @endforelse

                                        </div>


                                         <!-- Rejected Reason -->
                                            @if($file->status === 'rejected' && ($file->rejection_reason || $file->admin_reject))

                                                <div class="alert alert-danger rounded-3 mb-3 py-2 px-3">

                                                    <div class="d-flex align-items-start">

                                                        <i class="fa-solid fa-circle-exclamation me-2 mt-1"></i>

                                                        <div class="w-100">

                                                            <div class="fw-semibold mb-1">
                                                                Reason for Rejection
                                                            </div>

                                                            <div class="mb-1">
                                                                <strong>
                                                                    @if(!empty($file->admin_reject))
                                                                        Admin
                                                                    @else
                                                                        {{ $file->adviser }}
                                                                    @endif
                                                                </strong>
                                                            </div>

                                                            <div class="small text-secondary d-flex align-items-center flex-wrap gap-3">

                                                                <div>
                                                                    <small>
                                                                        {{ $file->rejected_at?->format('M d, Y') ?? $file->updated_at?->format('M d, Y') }}
                                                                    </small>
                                                                </div>

                                                                <div>
                                                                    <small>
                                                                        {{ $file->rejected_at?->format('h:i A') ?? $file->updated_at?->format('h:i A') }}
                                                                    </small>
                                                                </div>

                                                            </div>

                                                            <div class="small mt-1 mb-2">
                                                                {{ $file->admin_reject ?? $file->rejection_reason }}
                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            @endif


                                    <div
                                        class="alert alert-primary d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                        <div>
                                            <div>
                                                <div class="fw-bold">Full Research Document</div>
                                                <small>{{ basename($file->file_path) }}</small>
                                            </div>
                                        </div>



                                        <div class="d-flex gap-2 mt-3 mt-md-0">

                                           <!--<a data-turbo="false" href="{{ route('file.view', $file->id) }}" target="_blank"
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>-->

                                            <a data-turbo="false" href="{{ route('file.view', ['id' => $file->id, 'v' => time()]) }}" target="_blank"
                                                class="btn btn-outline-primary px-2 py-1">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>

                                            <a data-turbo="false" href="{{ route('file.download', $file->id) }}"
                                            class="btn btn-outline-success px-2 py-1">
                                                <i class="bi bi-download me-2"></i>
                                                Download
                                            </a>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
               @endforeach
            </div>
        </div>



                        <!--=================================== REQUEST ====================================-->
<div class="tab-pane fade" id="request">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <div>
            <h4 class="fw-bold mb-0">Access Requests</h4>
            <p class="text-muted small mb-0">Review who has requested access to your research files.</p>
        </div>
        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
            {{ $accessRequests->count() }} {{ Str::plural('request', $accessRequests->count()) }}
        </span>
    </div>

    <div class="d-flex flex-column gap-2 w-100">

        @forelse ($accessRequests as $request)

            @php
                $status = strtolower($request->status);
                $badge = match ($status) {
                    'approved' => 'bg-success-subtle text-success-emphasis',
                    'rejected', 'denied' => 'bg-danger-subtle text-danger-emphasis',
                    default => 'bg-warning-subtle text-warning-emphasis',
                };
            @endphp

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body py-2 px-3">


                {{-- Timestamp --}}
                    <div class="text-muted mt-1" style="font-size: .7rem;">
                        Requested {{ $request->created_at?->diffForHumans() }}
                    </div>


                    {{-- Top row: title + status --}}
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="fw-semibold mb-0 text-truncate" title="{{ $request->file->title ?? 'Research unavailable' }}">
                            {{ $request->file->title ?? 'Research unavailable' }}
                        </h6>
                        <span class="badge rounded-pill {{ $badge }}" style="font-size: .7rem;">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>

                    {{-- Requester --}}
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold me-2"
                             style="width: 30px; height: 30px; font-size: .8rem; flex-shrink: 0;">
                            {{ strtoupper(substr($request->name, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden lh-sm">
                            <div class="small fw-semibold text-truncate">{{ $request->name }}</div>
                            <a href="mailto:{{ $request->email }}" class="text-muted text-decoration-none text-truncate d-block" style="font-size: .75rem;">
                                {{ $request->email }}
                            </a>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="bg-light rounded-2 px-2 py-1">
                        <span class="text-muted fw-semibold" style="font-size: .7rem;">REASON:</span>
                        <span class="small">{{ $request->reason_access }}</span>
                    </div>

                                    {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center mt-2">

                    <div class="d-flex gap-2">
                        <form action="{{ route('access-requests.approve', $request->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="btn btn-sm bg-success-subtle text-success-emphasis border-success-subtle rounded-3 px-3"
                                    @disabled($status !== 'pending')>
                                Approve
                            </button>
                        </form>

                        <form action="{{ route('access-requests.reject', $request->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="btn btn-sm bg-danger-subtle text-danger-emphasis border-danger-subtle rounded-3 px-3"
                                    @disabled($status !== 'pending')>
                                Reject
                            </button>
                        </form>
                    </div>
                </div>

                </div>
            </div>

        @empty

            <div class="text-center py-4 bg-light rounded-3 border">
                <div class="fs-3 mb-1">📭</div>
                <h6 class="fw-semibold mb-0">No access requests yet</h6>
                <p class="text-muted small mb-0">New requests will appear here.</p>
            </div>

        @endforelse

    </div>
</div>











        <!-- ================= NOTIFICATIONS ================= -->
         <div class="tab-pane fade" id="notification">
            <div class="card mt-3 shadow-sm border-0 rounded-4">
            <div class="card-body">
            <h5 class="fw-bold"> Notifications </h5>
        @forelse($notifications as $notification)

    <div class="border-bottom py-3">

        @if($notification->created_at == $notification->updated_at)

            <div class="fw-semibold text-primary">
                <i class="fa-solid fa-upload"></i>
                 Research Submitted
            </div>

            <div>
                You submitted
                <strong>{{ $notification->title }}</strong>.
            </div>

        @elseif($notification->status == 'approved')

            <div class="fw-semibold text-success">
                <i class="fa-regular fa-circle-check"></i>
                 Research Approved
            </div>

            <div>
                Your research
                <strong>{{ $notification->title }}</strong>
                has been approved by the admin.
            </div>

        @elseif($notification->status == 'rejected')

            <div class="fw-semibold text-danger">
                <i class="fa-regular fa-circle-xmark"></i>
                 Research Rejected
            </div>

            <div>
                Your research
                <strong>{{ $notification->title }}</strong>
                has been rejected by the admin.
            </div>

        @else

            <div class="fw-semibold text-warning">
                ⏳ Research Pending
            </div>

            <div>
                Your research
                <strong>{{ $notification->title }}</strong>
                is currently under review.
            </div>

        @endif

        <small class="text-muted">
            {{ $notification->updated_at->diffForHumans() }}
        </small>

    </div>

@empty

    <p class="text-secondary">
        No notifications yet.
    </p>

@endforelse
</div>
</div>
</div>



        <!-- ================= BOOKMARKS ================= -->
        <div class="tab-pane fade" id="bookmark">

            <div class="card mt-3 shadow-sm border-0 rounded-4">

                <div class="card-body">

                    <h5 class="fw-bold">
                        Bookmarks
                    </h5>

                    <div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">

    </div>


</div>

                </div>

            </div>

        </div>

    </div>
@endif


    <!-- The SDG "Done" button and PDF dropzone behavior for the redesigned
         submission form are now handled by resources/js/research-submission.js
         (initSdgDoneButtons / initDropzone), loaded on demand by resources/js/app.js. -->

@if ($errors->any())
    <script>
        // A failed Submit/Update Research validation returns the user to
        // this page via back()->withErrors() — but the form itself lives in
        // a `popover="manual"` panel that is closed by default, so without
        // this the errors rendered above are in the DOM but never seen.
        // _popover_target (set on each form) says which panel to reopen.
        (() => {
            const target = @json(old('_popover_target', 'popover'));
            const panel = document.getElementById(target);
            if (panel && typeof panel.showPopover === 'function') {
                try {
                    panel.showPopover();
                } catch (e) {
                    // Already open.
                }
            }
        })();
    </script>
@endif

<!--js manage-->
<script>
  function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === "password") {
      input.type = "text";
      btn.textContent = "Hide";
    } else {
      input.type = "password";
      btn.textContent = "Show";
    }
  }
</script>


<script>
document.getElementById('logoutBtn').closest('form').addEventListener('submit', function () {
    const btn = document.getElementById('logoutBtn');
    document.getElementById('logoutIcon').classList.add('d-none');
    document.getElementById('logoutSpinner').classList.remove('d-none');
    document.getElementById('logoutText').textContent = 'Logging out...';
    setTimeout(() => btn.disabled = true, 0);
});
</script>



@endsection
