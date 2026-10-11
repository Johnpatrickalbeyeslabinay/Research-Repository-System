@extends('layouts.admin_layout')

@section('title', 'Admin Dashboard')

@section('admin')



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









<!--DASHBOARD-->
@if($view == 'dashboard')

    <div>
            <h3 class="fw-bold mb-1">
                Admin Dashboard
            </h3>

            <p class="text-secondary mb-0">
                Comprehensive overview of the research repository system — including Submission tracking, and engagement analytics.
            </p>
    </div>














<!--mga counts ng users etc.-->
   <div class="container-fluid mt-3">

    <div class="row g-3 stats-scroll">

        <!-- Total Users -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card shadow-sm border rounded-3">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between">
                        <div class="bg-light rounded-3 p-2">
                            <i class="bi bi-people fs-4 text-secondary"></i>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-secondary">Total Users</small>
                        <h4 class="fw-bold mb-0">{{ $userCounts->sum() }}</h4>
                    </div>

                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card shadow-sm border rounded-3">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between">
                        <div class="bg-light rounded-3 p-2">
                            <i class="bi bi-mortarboard fs-4 text-primary"></i>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-secondary">Total Students</small>
                        <h4 class="fw-bold mb-0">{{ $userCounts->get('student', 0) }}</h4>
                    </div>

                </div>
            </div>
        </div>

        <!-- Total Instructors -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card shadow-sm border rounded-3">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between">
                        <div class="bg-light rounded-3 p-2">
                            <i class="bi bi-person-workspace fs-4 text-success"></i>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-secondary">Total Instructors</small>
                        <h4 class="fw-bold mb-0">{{ $userCounts->get('instructor', 0) }}</h4>
                    </div>

                </div>
            </div>
        </div>

        <!-- Total Research -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card shadow-sm border rounded-3">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between">
                        <div class="bg-light rounded-3 p-2">
                            <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-secondary">Total Research</small>
                        <h4 class="fw-bold mb-0">{{ $totalResearch }}</h4>
                    </div>

                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card shadow-sm border rounded-3">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-2">
                            <i class="bi bi-clock-history fs-4 text-warning"></i>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-secondary">Pending Approvals</small>
                        <h4 class="fw-bold mb-0">{{ $pendingCount }}</h4>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>










<!--btn ng mga buttons-->
<div class="tab-content">
 <div class="bg-white rounded-5 shadow-sm d-inline-flex gap-2 nav nav-pills mt-3" id="dashboardTab" role="tablist">

        <!-- GRAPH -->
        <button class="nav-link active rounded-5 px-2 py-1" id="overview-tab" data-bs-toggle="pill"
            data-bs-target="#overview" type="button" role="tab">
            Graph
        </button>

        <!-- MY SUBMISSIONS -->
        <button class="nav-link rounded-5 px-2 py-1" id="submissions-tab"
            data-bs-toggle="pill" data-bs-target="#submissions" type="button" role="tab">

            Student Submissions

        </button>

        <!-- RE-SUBMIT -->
        <button class="nav-link rounded-5 px-2 py-1" id="resubmissions-tab"
            data-bs-toggle="pill" data-bs-target="#resubmissions" type="button" role="tab">

            Resubmissions

        </button>

        <!-- BACK-UP & Restore -->
        <button class="nav-link rounded-5 px-2 py-1" id="backup-tab"
            data-bs-toggle="pill" data-bs-target="#backup" type="button" role="tab">

            Bacup & Restore

        </button>

    </div>







    <!--Content ng mga Buttons-->
    <!--Nilalaman ng mga btn-->

 <!-- OVERVIEW BTN -->
<div class="tab-pane fade show active" id="overview">

    <div class="mt-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Overview Contents</h3>
        </div>

        <div class="row g-4">
        <!-- Pie Chart -->
            <div class="col-12">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div id="pieChart" style="width:100%; height:350px;"></div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card border-0 shadow-sm mt-3 rounded-4 h-100">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-bar-chart me-2"></i>Monthly Status Breakdown
                </h5>
                <p class="text-muted small mb-0">Approved · Pending · Rejected per month</p>
            </div>

            <select id="monthlyStatusYear" class="form-select form-select-sm w-auto"></select>
        </div>

        <div id="monthlyStatusChart" class="monthly-status-chart"></div>
    </div>
</div>

<style>
    .monthly-status-chart { width: 100%; height: 350px; }
    @media (max-width: 767.98px) { .monthly-status-chart { height: 300px; } }
    @media (max-width: 575.98px) { .monthly-status-chart { height: 280px; } }
</style>



    </div>

</div>













<!--MY SUBMISSIONS BUTTONS-->
<div class="tab-pane fade" id="submissions">
    <div class="container-fluid py-4">
<div class="card rounded-5 shadow-sm border-0">
<div class="card-body p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <h3 class="fw-bold mb-3 mb-md-0">
            All Research Submissions
        </h3>

        <!--<div class="d-flex flex-wrap gap-3">

            <span>
                <i class="bi bi-circle-fill text-success small"></i>
                {{ $approvedCount }}
            </span>

            <span>
                <i class="bi bi-circle-fill text-warning small"></i>
                {{ $pendingCount }}
            </span>

            <span>
                <i class="bi bi-circle-fill text-danger small"></i>
                {{ $rejectedCount }}
            </span>

        </div>-->

    </div>

    {{-- Was UserFile::where('status', 'admin_pending')->get() with no
         other option — once approved/rejected, a submission fell out of
         this list permanently, making the Approved/Rejected badge
         branches below unreachable. --}}
    <form method="GET" class="mb-3">

    <div class="d-flex align-items-center gap-2 flex-wrap">

        {{-- Status --}}
        <select name="status"
                class="form-select form-select-sm"
                style="width: 220px;"
                onchange="this.form.submit()">

            <option value="admin_pending"
                {{ $statusFilter === 'admin_pending' ? 'selected' : '' }}>
               For Admin Review
            </option>

            <option value="approved"
                {{ $statusFilter === 'approved' ? 'selected' : '' }}>
                Approved
            </option>

            <option value="rejected"
                {{ $statusFilter === 'rejected' ? 'selected' : '' }}>
                Rejected
            </option>

            <option value="pending"
                {{ $statusFilter === 'pending' ? 'selected' : '' }}>
                With adviser
            </option>

            <option value="all"
                {{ $statusFilter === 'all' ? 'selected' : '' }}>
                All statuses
            </option>

        </select>


        {{-- Year From --}}
        <select name="year_from"
                class="form-select form-select-sm"
                style="width: 120px;">

            <option value="">From Year</option>

            @for($year = 2020; $year <= 2026; $year++)
                <option value="{{ $year }}"
                    {{ request('year_from') == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endfor

        </select>


        <span class="text-muted small">to</span>


        {{-- Year To --}}
        <select name="year_to"
                class="form-select form-select-sm"
                style="width: 120px;">

            <option value="">To Year</option>

            @for($year = 2020; $year <= 2026; $year++)
                <option value="{{ $year }}"
                    {{ request('year_to') == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endfor

        </select>


        {{-- Filter --}}
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

    </div>

</form>

    <!-- Table -->
<div class="table-responsive">

        <div class="research-card">
    <div class="table-responsive">
        <table class="table align-middle research-table">

            <thead>
                <tr>
                    <th>Title</th>
                    <th>Researchers</th>
                    <th>Adviser</th>
                    <th>Submitted</th>
                    <th>Views</th>
                    <th>Downloads</th>
                    <th>Status</th>
                    <th>Visibility</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($studFiles as $file)

                <tr>

                    <!-- FILE TITLE -->
                    <td class="col-title text-truncate" title="{{ $file->title }}">
                        {{ $file->title }}
                    </td>

                    <!-- RESEARCHERS -->
                    {{-- Was $file->name — the submitter only. Every co-author
                         and guest researcher added through the researcher
                         picker was invisible to the admin. --}}
                    <td class="col-researchers" title="{{ implode(', ', $file->researcherNames()) }}">
                        {{ implode(', ', $file->researcherNames()) }}
                    </td>

                    <!-- ADVISER -->
                    @php
                        // Works whether $file->adviser is an array or a string like "Dr. Ramos; Engr. Lopez"
                        $advisers = collect(
                            is_array($file->adviser)
                                ? $file->adviser
                                : preg_split('/\s*(?:;|,|\/|&|\band\b|\n)\s*/i', (string) $file->adviser)
                        )->map(fn ($name) => trim($name))->filter()->values();
                    @endphp

                    <td class="text-muted col-nowrap" title="{{ $advisers->implode(', ') }}">
                        @if($advisers->isEmpty())
                            —
                        @else
                            {{ $advisers->first() }}
                            @if($advisers->count() > 1)
                                <span class="badge bg-light text-secondary border rounded-pill ms-1"
                                    style="font-size: 0.6875rem;">
                                    +{{ $advisers->count() - 1 }}
                                </span>
                            @endif
                        @endif
                    </td>

                    <!-- DATE SUBMITTED -->
                    <td class="col-nowrap">{{ $file->created_at->format('M d, Y') }}</td>

                    <!-- VIEWS -->
                    <td>
                        <span class="stat">
                            <i class="bi bi-eye"></i>
                            {{ $file->views ?? 0 }}
                        </span>
                    </td>

                    <!-- DOWNLOADS -->
                    <td>
                        <span class="stat">
                            <i class="bi bi-download"></i>
                            {{ $file->downloads ?? 0 }}
                        </span>
                    </td>

                    <!-- STATUS -->
                    <td>
                        @if($file->status === 'pending')
                            <span class="badge bg-info-subtle text-info-emphasis rounded-pill">
                                <i class="fa-solid fa-user-tie fa-2xs me-1"></i>
                                Under Review
                            </span>

                        @elseif($file->status === 'admin_pending')
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
                                <i class="fa-solid fa-user-shield fa-2xs me-1"></i>
                                For Final Approval
                            </span>

                        @elseif($file->status === 'approved')
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill">
                                <i class="fa-solid fa-circle-check fa-2xs me-1"></i>
                                Approved
                            </span>

                        @elseif($file->status === 'rejected')
                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">
                                <i class="fa-solid fa-circle-xmark fa-2xs me-1"></i>
                                Rejected
                            </span>
                        @endif
                    </td>

                    {{-- Was never shown at all — approve() silently publishes
                         to the repository whenever requested_visibility is
                         'public', with nothing here telling the admin that
                         was even asked for. --}}
                    <td>
                        @if($file->visibility === 'public')
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill"><i class="fa-solid fa-globe fa-2xs me-1"></i>Public</span>
                        @elseif($file->requested_visibility === 'public')
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Public requested</span>
                        @else
                            <span class="badge bg-light text-secondary border rounded-pill"><i class="fa-solid fa-lock fa-2xs me-1"></i>Private</span>
                        @endif
                    </td>

                    <!-- ACTIONS -->
                    <td class="text-end">

                        <div class="d-flex justify-content-end gap-1">

                            <button type="button"
                                    class="btn btn-outline-primary btn-action"
                                    popovertarget="popover-{{ $file->id }}">
                                <i class="bi bi-folder me-1"></i>View
                            </button>

                            <form action="{{ route('admin.files.destroy', $file->id) }}"
                                  method="POST"
                                  class="m-0">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="btn btn-outline-danger btn-action"
                                        onclick="return confirm('Are you sure you want to delete this research?')">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>

                            </form>

                        </div>


                        <!-- POPOVER: FILE CONTENT -->
                        <div id="popover-{{ $file->id }}" popover style="
                            border: 1px solid #dee2e6;
                            width: 100vw;
                            max-width: 100vw;
                            height: 100vh;
                            max-height: 100vh;
                            margin: 0;
                            padding: 0;
                            overflow-y: auto;
                            inset: 0;
                            font-size: 1rem;
                        ">


                        <button type="button" popovertarget="popover-{{ $file->id }}" popovertargetaction="hide" class="btn-close position-absolute top-0 end-0 m-3"
                        aria-label="Close">
                    </button>
                            <div class="container">
                                <div class="p-3 text-start mt-3">

                                    <h3 class="fw-bold mb-3">{{ $file->title }}</h3>

                                    <div>
                                        <x-research.meta :file="$file" />
                                    </div>

                                    <div class="mb-4 small">
                                        <span class="text-muted">Submitted:</span> {{ $file->created_at->format('M j, Y') }}
                                    </div>

                                    <h6 class="fw-semibold">Abstract</h6>
                                    <p class="text-muted" style="text-align: justify;">
                                        {{ $file->desc ?? 'No abstract available.' }}
                                    </p>

                                    @if($file->instructor_feedback)
                                        {{-- Was shown to the instructor only — the admin made the
                                             final call with no visibility into what the adviser
                                             actually said about the paper. --}}
                                        <h6 class="fw-semibold mt-4">Adviser Feedback</h6>
                                        <p class="text-muted">{{ $file->instructor_feedback }}</p>
                                    @endif

                                    <div class="alert alert-primary d-flex justify-content-between align-items-center mt-4 flex-wrap">
                                        <div>
                                            <div class="fw-semibold">Full Research Document</div>
                                            <small>{{ basename($file->file_path) }}</small>
                                        </div>

                                        <div class="d-flex gap-2 mt-3 mt-md-0">
                                            <a data-turbo="false"
                                               href="{{ route('file.view', ['id' => $file->id, 'v' => time()]) }}"
                                               target="_blank"
                                               class="btn btn-outline-primary btn-action">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        </div>
                                    </div>

                                    <!-- ACTION -->
                                    <div class="d-flex gap-2">

                                        @if($file->status == 'admin_pending')

                                            <!-- APPROVE -->
                                            <form action="{{ route('research.approve', $file->id) }}"
                                                  method="POST"
                                                  class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-action">
                                                    <i class="fa-regular fa-circle-check me-1"></i>Approve
                                                </button>
                                            </form>

                                            <!-- REJECT -->
                                            <form action="{{ route('research.reject', $file->id) }}"
                                                  method="POST"
                                                  class="m-0">
                                                @csrf

                                                <button type="button"
                                                        class="btn btn-outline-danger btn-action"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal{{ $file->id }}">
                                                    <i class="fa-regular fa-circle-xmark me-1"></i>Reject
                                                </button>

                                                <!-- Reject Modal -->
                                                <div class="modal fade"
                                                     id="rejectModal{{ $file->id }}"
                                                     tabindex="-1"
                                                     aria-labelledby="rejectModalLabel{{ $file->id }}"
                                                     aria-hidden="true">

                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title fs-6"
                                                                    id="rejectModalLabel{{ $file->id }}">
                                                                    Reject Research Paper
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <p class="mb-3 small">
                                                                    Please provide a reason for rejecting this research paper.
                                                                </p>

                                                                <label class="form-label fw-semibold small">
                                                                    Rejection Reason / Comment
                                                                </label>

                                                                <textarea name="admin_reject"
                                                                          class="form-control form-control-sm"
                                                                          rows="5"
                                                                          placeholder="Please specify the reason for rejection..."
                                                                          required></textarea>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                                                    Cancel
                                                                </button>

                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fa-regular fa-circle-xmark me-1"></i>Confirm Reject
                                                                </button>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </form>

                                        @endif

                                    </div>

                                </div>
                            </div>
                        </div>

                    </td>
                </tr>

                @endforeach
            </tbody>

        </table>
    </div>
</div>

            <div class="d-flex justify-content-center mt-3">
                {{ $studFiles->links() }}
            </div>

            </div>

        </div>

    </div>

</div>


</div>



            <!--MY RESUBMISSIONS BUTTONS-->
            <div class="tab-pane fade" id="resubmissions">
                <div class="container-fluid py-4">
            <div class="card rounded-5 shadow-sm border-0">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

                    <h3 class="fw-bold mb-3 mb-md-0">
                        All Research Resubmissions
                    </h3>
            </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light text-uppercase text-secondary" style="font-size: 11px; letter-spacing: .04em;">
                                    <tr>
                                        <th class="ps-3 py-2 fw-semibold">Research</th>
                                        <th class="py-2 fw-semibold">Student</th>
                                        <th class="py-2 fw-semibold text-center">Resubmissions</th>
                                        <th class="pe-3 py-2 fw-semibold text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($limitReachedFiles as $file)
                                <tr>
                                    <td class="ps-3 py-2" style="max-width: 280px;">
                                        <div class="fw-semibold text-truncate" title="{{ $file->title }}">
                                            {{ $file->title }}
                                        </div>
                                    </td>

                                    <td class="py-2 text-secondary">
                                        {{ $file->user->name ?? 'Unknown' }}
                                    </td>

                                    <td class="py-2 text-center">
                                        <span class="fw-semibold">{{ $file->resubmit_count }}</span>
                                        / {{ $file::MAX_RESUBMITS }}
                                    </td>

                                    <td class="pe-3 py-2 text-end">
                                        <form
                                            action="{{ route('admin.research.reset-resubmits', $file->id) }}"
                                            method="POST"
                                            class="d-inline m-0"
                                            onsubmit="return confirm('Reset the resubmit limit for this research?')"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="btn btn-sm bg-danger-subtle text-danger-emphasis border-danger-subtle rounded-2 px-2 py-1"
                                                style="font-size: 12px;"
                                            >
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                Reset
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle me-1"></i>
                                        No research has reached the resubmission limit.
                                    </td>
                                </tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
            </div>
            </div>




 <!-- Backup & Restore -->
<div class="tab-pane fade" id="backup">

    <div class="mt-3">

        {{-- FLASH MESSAGES
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif--}}


    {{-- PAGE HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Backup Management</h3>
            <p class="text-secondary mb-0">
                Manage database backups and restore system data.
            </p>
        </div>
    </div>


    {{-- SUMMARY CARDS --}}
    <div class="row g-3 mb-4">

        {{-- LAST BACKUP --}}
        <div class="col-md-4">
            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-primary-subtle h-100">

                <div class="bg-primary text-white rounded-3 px-3 py-2 fs-3">
                    <i class="bi bi-clock"></i>
                </div>

                <div>
                    <div class="text-primary text-uppercase small">
                        Last Backup
                    </div>

                    <div class="fw-semibold text-primary-emphasis">
                        @if ($lastBackup)
                            {{ $lastBackup->created_at->format('Y-m-d H:i:s') }}
                        @else
                            No backup yet
                        @endif
                    </div>
                </div>

            </div>
        </div>


        {{-- TOTAL BACKUPS --}}
        <div class="col-md-4">
            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-success-subtle h-100">

                <div class="bg-success text-white rounded-3 px-3 py-2 fs-3">
                    <i class="bi bi-hdd"></i>
                </div>

                <div>
                    <div class="text-success text-uppercase small">
                        Total Backups
                    </div>

                    <div class="fw-semibold text-success-emphasis">
                        {{ $totalBackups }}
                        {{ $totalBackups == 1 ? 'snapshot' : 'snapshots' }}
                        stored
                    </div>
                </div>

            </div>
        </div>


        @php
            $now = now();

            $nextScheduled = $now->copy()->setTime(23, 0, 0);

            if ($now->greaterThanOrEqualTo($nextScheduled)) {
                $nextScheduled->addDay();
            }
        @endphp
        {{-- NEXT SCHEDULED --}}
        {{-- NEXT SCHEDULED --}}
<div class="col-md-4">
    <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-info-subtle h-100">

        <div class="bg-info text-white rounded-3 px-3 py-2 fs-3">
            <i class="bi bi-shield-check"></i>
        </div>

        <div>
            <div class="text-info text-uppercase small">
                Next Scheduled
            </div>

            <div class="fw-semibold text-info-emphasis">
                {{ $nextScheduled->isToday()
                    ? 'Today at '.$nextScheduled->format('g:i A')
                    : ($nextScheduled->isTomorrow()
                        ? 'Tomorrow at '.$nextScheduled->format('g:i A')
                        : $nextScheduled->format('M d, Y').' at '.$nextScheduled->format('g:i A'))
                }}
            </div>
        </div>

    </div>
</div>

    </div>


    {{-- MANUAL BACKUP --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4 d-flex flex-wrap gap-3 justify-content-between align-items-center">

            <div>
                <h5 class="fw-semibold mb-1">
                    Manual Backup
                </h5>

                <div class="text-secondary">
                    Create an on-demand snapshot of all system data and records.
                </div>
            </div>


            <div class="d-flex gap-2">
                {{-- DOWNLOAD LATEST BACKUP --}}
                @if ($lastBackup)

                    <a href="{{ route('admin.backups.download', $lastBackup) }}"
                       class="btn btn-light border">

                        <i class="bi bi-download me-2"></i>
                        Download DB
                    </a>
                @else
                    <button type="button"
                            class="btn btn-light border"
                            disabled>
                        <i class="bi bi-download me-2"></i>
                        Download DB
                    </button>
                @endif


                {{-- CREATE BACKUP --}}
                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createModal">
                    <i class="bi bi-database-add me-2"></i>
                    Create Backup Now
                </button>
            </div>
        </div>
    </div>


    {{-- BACKUP HISTORY --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-1">
                Backup History
            </h5>
            <div class="text-secondary mb-4">
                View and download database snapshots created by the system.
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Snapshot</th>
                            <th>Type</th>
                            <th>Timestamp</th>
                            <th>Size</th>
                            <th>Records</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                {{-- NAME --}}
                                <td class="fw-semibold">
                                    {{ $backup->name }}
                                </td>
                                {{-- TYPE --}}
                                <td>
                                    @if ($backup->type === 'automatic')
                                        <span class="badge rounded-pill text-bg-light">
                                            <i class="bi bi-stopwatch me-1"></i>
                                            Auto
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-primary">
                                            <i class="bi bi-dot"></i>
                                            Manual
                                        </span>
                                    @endif
                                </td>

                                {{-- TIMESTAMP --}}
                                <td class="text-secondary">
                                    {{ $backup->created_at->format('Y-m-d H:i:s') }}
                                </td>

                                {{-- SIZE --}}
                                <td class="text-secondary">
                                    @if ($backup->file_size)
                                        {{ number_format($backup->file_size / 1024 / 1024, 2) }} MB
                                    @else
                                        N/A
                                    @endif
                                </td>

                                {{-- RECORDS --}}
                                <td class="text-secondary">
                                    {{ $backup->records_count ?? 0 }}
                                </td>

                                {{-- ACTION --}}
                                <td class="text-end">
                                    <form method="POST"
                                            action="{{ route('admin.backups.restore', $backup) }}"
                                            class="d-inline">
                                            @csrf

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                Restore
                                            </button>
                                        </form>
                                </td>
                            </tr>
                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center text-secondary py-5">

                                    <i class="bi bi-database-x fs-1 d-block mb-2"></i>

                                    No backups have been created yet.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- WARNING --}}
    <div class="alert alert-warning rounded-4 d-flex gap-3 align-items-start"
         role="alert">

        <i class="bi bi-exclamation-triangle fs-5"></i>

        <div>

            <strong>Before you restore:</strong>

            restoring a snapshot replaces current database data
            with the data contained in that backup.

            Create a manual backup first if you want to keep your
            latest changes.

        </div>

    </div>

</main>


{{-- ========================================================= --}}
{{-- CREATE BACKUP MODAL --}}
{{-- ========================================================= --}}

<div class="modal fade"
     id="createModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0 shadow">

            <form method="POST"
                  action="{{ route('admin.backups.store') }}">

                @csrf

                <div class="modal-header border-0">

                    <h5 class="modal-title fw-semibold">
                        Create Database Backup
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>

                </div>


                <div class="modal-body">

                    <div class="text-center py-2">

                        <div class="bg-primary-subtle
                                    text-primary
                                    rounded-circle
                                    d-inline-flex
                                    align-items-center
                                    justify-content-center
                                    mb-3"
                             style="width: 70px; height: 70px;">

                            <i class="bi bi-database-add fs-2"></i>

                        </div>


                        <h5 class="fw-semibold">
                            Create a new backup?
                        </h5>


                        <p class="text-secondary mb-0">

                            The system will create a complete PostgreSQL
                            database snapshot.

                        </p>

                    </div>

                </div>


                <div class="modal-footer border-0">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>


                    <button type="submit"
                            class="btn btn-primary">

                        <i class="bi bi-database-add me-2"></i>

                        Create Backup

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


    </div>

</div>
            </div>

@endif





<!--MANAGE USERS-->
@if($view == 'user')

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

        <button type="button"
                        class="btn btn-success rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#importStudentsModal"> <i class="bi bi-file-earmark-excel me-2"></i>
                    Import Student Accounts </button>


    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">

        <i class="fa-solid fa-plus"></i>
        Add User
    </button>



    <!-- Add User Modal -->
                <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 shadow">

                            <!-- Header -->
                            <div class="modal-header border-0 px-4 pt-4">
                                <h5 class="fw-bold mb-0">Add New User</h5>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Close">
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="modal-body px-4 pb-4">

                                <form action="{{ route('admin.store') }}" method="POST">
                                    @csrf

                                    <!-- Role -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Role <span class="text-danger">*</span>
                                        </label>

                                        <select name="role"
                                                id="role"
                                                class="form-select rounded-3">
                                            <option value="student">Student</option>
                                            <option value="instructor">Instructor</option>
                                        </select>
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Email <span class="text-danger">*</span>
                                        </label>

                                        <input type="email"
                                            name="email"
                                            class="form-control rounded-3"
                                            placeholder="e.g. john.doe@example.com"
                                            required>
                                    </div>

                                    <!-- User ID -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            User ID <span class="text-danger">*</span>
                                        </label>

                                        <input type="text"
                                            name="user_id"
                                            class="form-control rounded-3"
                                            placeholder="e.g. 23-001"
                                            required>
                                    </div>

                                    <!-- Full Name -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Full Name <span class="text-danger">*</span>
                                        </label>

                                        <input type="text"
                                            name="name"
                                            class="form-control rounded-3"
                                            placeholder="e.g. Juan Dela Cruz"
                                            required>
                                    </div>

                                    <!-- Password -->
                                    <div class="alert alert-info d-flex align-items-center py-2">
                                        <i class="fa-solid fa-circle-info me-2"></i>

                                        <small>
                                            Password will be generated automatically after creating the account.
                                        </small>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="d-flex gap-2">

                                        <button type="submit"
                                                class="btn btn-primary flex-fill rounded-3 fw-semibold">
                                            <i class="fa-solid fa-user-plus me-2"></i>
                                            Create User
                                        </button>

                                        <button type="button"
                                                class="btn btn-light border rounded-3"
                                                data-bs-dismiss="modal">
                                            Cancel
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>
                    </div>
                </div>
        </div>
    </div>

    <!-- Edit User modal — single shared instance, populated via JS from the
         clicked row's data-* attributes (see script below) rather than one
         modal per row. -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">

                <div class="modal-header border-0 px-4 pt-4">
                    <h3 class="fw-bold">Edit User</h3>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body px-4 pb-4">

                    <form id="editUserForm" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Role <span class="text-danger">*</span>
                            </label>

                            <select name="role" id="editUserRole" class="form-select form-select-lg rounded-3">
                                <option value="student">Student</option>
                                <option value="instructor">Instructor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>

                            <input type="email" name="email" id="editUserEmail" class="form-control form-control-lg rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                User ID <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="user_id" id="editUserUserId" class="form-control form-control-lg rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" id="editUserName" class="form-control form-control-lg rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Year Level
                            </label>

                            <input type="text" name="year_level" id="editUserYearLevel" class="form-control form-control-lg rounded-3">
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary flex-fill py-3 rounded-3 fw-semibold">
                                Save Changes
                            </button>

                            <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>





                        <!-- Import Student Accounts Button -->



                    <!-- Import Student Accounts Modal -->

                    <div class="modal fade"
                        id="importStudentsModal"
                        tabindex="-1"
                        aria-labelledby="importStudentsModalLabel"
                        aria-hidden="true">


                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow rounded-4">

                            <div class="modal-header border-0 px-4 pt-4">
                                <div>
                                    <h5 class="modal-title fw-bold" id="importStudentsModalLabel">
                                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                        Import Student Accounts
                                    </h5>

                                    <p class="text-muted small mb-0 mt-1">
                                        Upload the Excel file containing the student information.
                                    </p>
                                </div>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Close">
                                </button>
                            </div>


                            <div class="modal-body px-4 pb-4">

                                <div class="alert alert-info border-0 rounded-3 small">
                                    <i class="bi bi-info-circle me-2"></i>
                                    The system will automatically create student accounts and generate temporary passwords.
                                </div>

                                <form data-turbo="false" action="{{ route('admin.students.import') }}"
                                    method="POST"
                                    enctype="multipart/form-data">

                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            Student Excel File
                                        </label>

                                        <input type="file"
                                            name="file"
                                            class="form-control"
                                            accept=".xlsx,.xls,.csv"
                                            required>

                                        <div class="form-text">
                                            Accepted formats: .xlsx
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">

                                        <button type="button"
                                                class="btn btn-light border rounded-3 px-1 py-1"
                                                data-bs-dismiss="modal">
                                            Cancel
                                        </button>

                                        <button type="submit"
                                                class="btn btn-success rounded-3 px-1 py-1">
                                            <i class="bi bi-upload me-2"></i>
                                            Import Students
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>
                    </div>

                    </div>













    <!--users total-->
    <div class="container-fluid py-4">



       <div class="row g-4 stats-scroll">

    <!-- Total Users -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Total Users</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->sum() }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-regular fa-user text-primary fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Students -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Students</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->get('student', 0) }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-solid fa-user-graduate text-primary fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Instructor -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Instructor</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->get('instructor', 0) }}
                    </h3>
                </div>

                <div class="bg-success bg-opacity-10 rounded-2 p-3">
                    <i class="fa-solid fa-user-check text-success fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Admin -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Admin</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->get('admin', 0) }}
                    </h3>
                </div>

                <div class="bg-warning bg-opacity-10 rounded-2 p-3">
                    <i class="fa-solid fa-shield text-warning fs-4"></i>
                </div>

            </div>
        </div>
    </div>

</div>











                <!--list od users-->

                <div class="container-fluid py-4 px-3 px-md-4">
                <div class="bg-white rounded-3 shadow-sm p-3 p-md-4">

                    <!-- Search + Filters -->
                <div class="d-flex flex-column flex-lg-row gap-2 align-items-stretch align-items-lg-center">

                    <!-- Search -->
                    <form method="GET" action="{{ route('admin') }}" class="input-group" style="max-width: 750px;">
                        <input type="hidden" name="view" value="user">
                        <input type="hidden" name="role" value="{{ $role }}">

                        <span class="input-group-text bg-light border-0 text-secondary px-2 py-1">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control bg-light border-0 px-2 py-1"
                            placeholder="Search by name, email, or ID...">

                        <button type="submit" class="btn btn-outline-secondary border-0 bg-light">Search</button>
                    </form>


                    <!-- Role Filters -->
                    <div class="d-flex flex-wrap gap-2" role="group" aria-label="Role filter">

                        <a href="{{ route('admin', ['view' => 'user']) }}"
                        class="btn px-2 py-1 {{ !$role ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All
                        </a>

                        <a href="{{ route('admin', ['view' => 'user', 'role' => 'student']) }}"
                        class="btn px-2 py-1 {{ $role === 'student' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Student
                        </a>

                        <a href="{{ route('admin', ['view' => 'user', 'role' => 'instructor']) }}"
                        class="btn px-2 py-1 {{ $role === 'instructor' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Instructor
                        </a>

                        <a href="{{ route('admin', ['view' => 'user', 'role' => 'admin']) }}"
                        class="btn px-2 py-1 {{ $role === 'admin' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Admin
                        </a>

                    </div>
                </div>


                    <p class="text-secondary small mt-3 mb-2">Showing {{ $users->total() }} user{{ $users->total() === 1 ? '' : 's' }}</p>

                    <!-- Table -->
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                        <tr class="small text-nowrap">
                            <th>Name</th>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>


                @forelse($users as $user)

                    <tr>
                        <td class="user-name fw-bold" style="font-size: 13px;">
                            {{ $user->name }}
                        </td>
                        <td class="user-id" style="font-size: 13px;">
                            {{ $user->user_id }}
                        </td>
                        <td class="user-email" style="font-size: 13px;">
                            <i class="fa-regular fa-envelope"></i>
                            {{ $user->email }}
                        </td>
                        <td>
                            <span class="role-badge" style="font-size: 13px;">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>

                        <td class="text-end" style="font-size: 13px;">
                            <button type="button" class="btn btn-outline-primary px-1 py-0"
                                data-bs-toggle="modal"
                                data-bs-target="#editUserModal"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-user-id="{{ $user->user_id }}"
                                data-role="{{ $user->role }}"
                                data-year-level="{{ $user->year_level }}">
                            <i class="fa-regular fa-pen-to-square fa-sm"></i>
                            Edit</button>


                            @if($user->account_status === 'active') <form action="{{ route('admin.users.toggle-status', $user->id) }}"
                                method="POST"
                                class="d-inline">
                            @csrf
                            @method('PATCH')


                                <button type="submit" class="btn btn-outline-warning px-1 py-0">
                                    <i class="fa-solid fa-user-slash fa-sm me-1"></i>
                                    Deactivate
                                </button>
                            </form>


                            @else <form action="{{ route('admin.users.toggle-status', $user->id) }}"
                                method="POST"
                                class="d-inline">
                            @csrf
                            @method('PATCH')


                                <button type="submit" class="btn btn-outline-success px-1 py-0">
                                    <i class="fa-solid fa-user-check fa-sm me-1"></i>
                                    Activate
                                </button>
                            </form>


                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center py-4">
                            No users found.
                        </td>
                    </tr>

                @endforelse

                </tbody>
                </table>
                    </div>

                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                    </div>
                    </div>
                    </div>
                    @endif








<!--==================UPLOAD EXISTING RESEARCH OF STUDENTS=================-->
@if($view == 'upload')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-cloud-arrow-up fa-2xl text-primary"></i>
                Upload Existing Research
            </h3>

            <p class="text-secondary mb-0">
                Add previous Fisheries Department research to the repository.
            </p>
        </div>



        <div class="archive-card mx-auto" style="max-width: 850px;">

    <div class="d-flex align-items-center gap-2 mb-1">
        {{-- HEADER --}}
    </div>

    <form id="archiveUploadForm"
          action="{{ route('admin.archive.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        {{-- DROPZONE --}}
        <label for="archiveFile" id="dropzone" class="dropzone-box d-block mb-4">
            <i class="bi bi-file-earmark-text dz-icon d-block mb-2" id="dzIcon"></i>

            <div class="dz-title" id="dzTitle">
                Choose a research paper
            </div>

            <div class="dz-hint mt-1" id="dzHint">
                Click to browse or drag and drop &middot; PDF only
            </div>

            <input type="file"
                   id="archiveFile"
                   name="file"
                   accept="application/pdf,.pdf"
                   class="d-none"
                   required>
        </label>

        @error('file')
            <div class="text-danger small mb-3 mt-n3">{{ $message }}</div>
        @enderror

        {{-- TITLE --}}
        <div class="mb-3">
            <label for="title" class="form-label">Research title</label>
            <input type="text"
                   id="title"
                   name="title"
                   value="{{ old('title') }}"
                   class="form-control @error('title') is-invalid @enderror"
                   placeholder="Enter the full title as it appears on the paper"
                   required>

            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- AUTHOR + YEAR --}}
        <div class="row g-3 mb-3">
            <div class="col-md-8">
                <label for="author" class="form-label">Researchers</label>
                <input type="text"
                       id="author"
                       name="author"
                       value="{{ old('author') }}"
                       class="form-control @error('author') is-invalid @enderror"
                       placeholder="e.g. Juan Dela Cruz, Maria Santos"
                       required>

                @error('author')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="year_published" class="form-label">Year published</label>
                <select id="year_published"
                        name="year_published"
                        class="form-select @error('year_published') is-invalid @enderror"
                        required>
                    <option value="" disabled {{ old('year_published') ? '' : 'selected' }}>
                        Select year
                    </option>

                    @for($year = now()->year; $year >= 2020; $year--)
                        <option value="{{ $year }}"
                            {{ old('year_published') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>

                @error('year_published')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- ABSTRACT --}}
        <div class="mb-3">
            <label for="abstract" class="form-label">Abstract</label>
            <textarea id="abstract"
                      name="abstract"
                      rows="4"
                      class="form-control @error('abstract') is-invalid @enderror"
                      placeholder="Paste or type the abstract from the archived paper"
                      required>{{ old('abstract') }}</textarea>

            @error('abstract')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- KEYWORDS --}}
        <div class="mb-4">
            <label for="keywords" class="form-label">Keywords</label>

            <input type="text"
                   id="keywords"
                   name="keywords"
                   value="{{ old('keywords') }}"
                   class="form-control @error('keywords') is-invalid @enderror"
                   placeholder="e.g. aquaculture, coastal management">

            <div class="form-text">
                Separate keywords with commas.
            </div>

            @error('keywords')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- WARNING --}}
        <div class="alert alert-warning py-3 px-3 mb-4 small" role="alert">
            <span>
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>

            <strong>Please review your information carefully.</strong>
            Once uploaded, this research will be published directly to the
            <strong>public repository</strong>.
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-end gap-2">
            <button type="button"
                    id="clearBtn"
                    class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-x-circle me-1"></i>Clear
            </button>

            <button type="submit"
                    class="btn btn-primary btn-sm px-3">
                <i class="bi bi-upload me-1"></i>Upload
            </button>
        </div>

    </form>
</div>
@endif






<!--==================REPORT=================-->
@if($view == 'report')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Report</h3>
            <p class="text-secondary mb-0">
                View and generate research repository activity reports.</p>
        </div>

        <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.reports.export', [
                        'year' => $year,
                        'period' => $period,
                    ]) }}"
                    class="btn btn-success">
                        <i class="fa-solid fa-file-excel me-1"></i>
                        Export to Excel
                    </a>


                   <button
                        type="button"
                        class="btn btn-outline-secondary px-2 py-1"
                        onclick="printReport()"
                    >
                        <i class="fa-solid fa-print"></i>
                        Print Report
                    </button>


        </div>
</div>


                    <!-- Counts -->
                <div class="row g-4 stats-scroll">

    <!-- Total Users -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Total Research Paper</p>
                    <h3 class="fw-normal mb-0">
                        {{ $totalResearch }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-regular fa-file-word text-primary fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Students -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Total Registered User</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->sum() }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-solid fa-users text-primary fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Instructor -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Total Instructor</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->get('instructor', 0) }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-solid fa-chalkboard-user text-primary fs-4 "></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Admin -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">Students</p>
                    <h3 class="fw-normal mb-0">
                        {{ $userCounts->get('student', 0) }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-solid fa-user-graduate text-primary fs-4"></i>
                </div>

            </div>
        </div>
    </div>

</div>


<!--=========================================GRAPH==========================================-->
<!--BAR CHART-->
<form class="filter-bar" id="reportFilterForm" method="GET" action="{{ route('admin') }}">
    <input type="hidden" name="view" value="report">

    <div>
        <label for="period" class="filter-label">
            Report Period
        </label>

        <select id="period" name="period" class="form-select">
            <option value="annually"
                @selected(($period ?? 'annually') === 'annually')>
                Annually
            </option>

            <option value="quarterly"
                @selected(($period ?? '') === 'quarterly')>
                Quarterly
            </option>

            <option value="monthly"
                @selected(($period ?? '') === 'monthly')>
                Monthly
            </option>

            <option value="weekly"
                @selected(($period ?? '') === 'weekly')>
                Weekly
            </option>
        </select>
    </div>


    <div>
        <label for="year" class="filter-label" >
            Year
        </label>

        <select id="year" name="year" class="form-select">
            @for ($y = now()->year; $y >= 2024; $y--)
                <option
                    value="{{ $y }}"
                    @selected((int) ($year ?? now()->year) === $y)
                >
                    {{ $y }}
                </option>
            @endfor
        </select>
    </div>


    <div>
        <label class="filter-label d-block">
            &nbsp;
        </label>

        <button
            type="submit"
            class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 py-2"
            title="Search report"
            aria-label="Search report">
            <i class="bi bi-search fs-5"></i>
        </button>
    </div>


    <div>
        <label class="filter-label d-block">
            &nbsp;
        </label>

        <a href="{{ route('admin', ['view' => 'report']) }}"
            class="btn btn-reset d-inline-flex align-items-center gap-2 px-3 py-2">
            <i class="bi bi-arrow-counterclockwise"></i>
            Reset
        </a>
    </div>
</form>




<!-- graph -->
<script>
    window.adminReportData = @json($reportData);
</>
<!-- REPORT AREA -->
<div id="adminPrintReport">

    <!-- RESEARCH + REGISTERED USERS -->
    <div class="row g-4">

        <!-- RESEARCH UPLOAD -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">

                    <h5 style="font-weight: bold; color: #072a89;">
                        Research Upload Activity
                    </h5>

                    <div
                        id="reportChart"
                        style="width: 100%; height: 420px;">
                    </div>

                </div>
            </div>
        </div>

        <!-- REGISTERED USERS -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">

                    <h5 style="font-weight: bold; color: #072a89;">
                        Registered User
                    </h5>

                    <div
                        id="registeredUsersChart"
                        style="width: 100%; height: 420px;">
                    </div>

                </div>
            </div>
        </div>

    </div>


    <!-- SEARCH KEYWORDS -->
    <div
        data-admin-charts
        class="card border-0 shadow-sm rounded-4 mt-5"
    >
        <div class="card-body p-4">

            <h5 style="font-weight: bold; color: #072a89;">
                Search Keywords
            </h5>

            <div
                id="searchKeywordsChart"
                style="width: 100%; height: 500px;"
            ></div>

        </div>
    </div>

</div>




@endif













<script>
document.getElementById('logoutBtn').closest('form').addEventListener('submit', function () {
    const btn = document.getElementById('logoutBtn');
    document.getElementById('logoutIcon').classList.add('d-none');
    document.getElementById('logoutSpinner').classList.remove('d-none');
    document.getElementById('logoutText').textContent = 'Logging out...';
    setTimeout(() => btn.disabled = true, 0);
});
</script>

<script>
        document.getElementById('editUserModal')?.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const form = document.getElementById('editUserForm');

            form.action = '/admin/users/' + btn.dataset.id;
            document.getElementById('editUserRole').value = btn.dataset.role;
            document.getElementById('editUserEmail').value = btn.dataset.email;
            document.getElementById('editUserUserId').value = btn.dataset.userId;
            document.getElementById('editUserName').value = btn.dataset.name;
            document.getElementById('editUserYearLevel').value = btn.dataset.yearLevel === 'null' ? '' : btn.dataset.yearLevel;
        });
    </script>

    <script>
    (function () {
        const form      = document.getElementById('archiveUploadForm');
        const input     = document.getElementById('archiveFile');
        const dropzone  = document.getElementById('dropzone');
        const dzIcon    = document.getElementById('dzIcon');
        const dzTitle   = document.getElementById('dzTitle');
        const dzHint    = document.getElementById('dzHint');
        const clearBtn  = document.getElementById('clearBtn');

        const defaults = {
            title: dzTitle.textContent,
            hint: dzHint.innerHTML
        };

        function showFile(file) {
            if (!file) return resetDropzone();

            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                alert('Only PDF files are accepted.');
                input.value = '';
                return resetDropzone();
            }

            dropzone.classList.add('has-file');
            dzIcon.className = 'bi bi-file-circle-check dz-icon d-block mb-2 text-success';
            dzTitle.textContent = file.name;
            dzHint.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB · Click to replace';
        }

        function resetDropzone() {
            dropzone.classList.remove('has-file', 'is-dragover');
            dzIcon.className = 'bi bi-file-earmark-text dz-icon d-block mb-2';
            dzTitle.textContent = defaults.title;
            dzHint.innerHTML = defaults.hint;
        }

        input.addEventListener('change', () => showFile(input.files[0]));

        ['dragenter', 'dragover'].forEach(evt =>
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            })
        );

        ['dragleave', 'drop'].forEach(evt =>
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
            })
        );

        dropzone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showFile(input.files[0]);
            }
        });

        clearBtn.addEventListener('click', () => {
            form.reset();
            form.querySelectorAll('input[type="text"], textarea').forEach(el => el.value = '');
            form.querySelector('select').selectedIndex = 0;
            resetDropzone();
        });
    })();
</script>


<script>
    (function () {
        const form   = document.getElementById('reportFilterForm');
        const label  = document.getElementById('sdgLabel');
        const boxes  = form.querySelectorAll('input[name="sdg[]"]');

        function updateLabel() {
            const checked = Array.from(boxes).filter(b => b.checked);

            if (checked.length === 0) {
                label.textContent = 'All SDGs';
            } else if (checked.length <= 2) {
                label.textContent = checked.map(b => b.dataset.label).join(', ');
            } else {
                label.textContent = checked.length + ' SDGs selected';
            }
        }

        boxes.forEach(b => b.addEventListener('change', updateLabel));

        // Reset button: wait for the form to clear, then refresh the label
        form.addEventListener('reset', () => setTimeout(updateLabel, 0));

        updateLabel();
    })();
</script>


<script>
    function printReport() {
        window.print();
    }
</script>


<script>
    function printReport() {
        document.body.classList.add('printing-report');

        // Bigyan ang ECharts ng oras para mag-render/resize
        setTimeout(() => {
            window.print();
        }, 300);
    }

    window.addEventListener('afterprint', function () {
        document.body.classList.remove('printing-report');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var pieChart = echarts.init(document.getElementById('pieChart'));

    var option = {
        title: {
            text: 'Research Status Distribution',
            left: 'left',
            textStyle: { color: '#1F2937', fontSize: 13 }
        },
        tooltip: { trigger: 'item' },
        legend: { bottom: 10, left: 'center' },
        series: [{
            name: 'Research Status',
            type: 'pie',
            radius: ['40%', '70%'],
            avoidLabelOverlap: false,
            label: { show: false, position: 'center' },
            emphasis: {
                label: { show: true, fontSize: 24, fontWeight: 'bold' }
            },
            labelLine: { show: false },
            data: [
    { value: {{ $approvedCount }}, name: 'Approved', itemStyle: { color: '#0F9D8A' } },
    { value: {{ $pendingCount }},  name: 'Pending',  itemStyle: { color: '#E8A93B' } },
    { value: {{ $rejectedCount }}, name: 'Rejected', itemStyle: { color: '#D9534F' } }
],
        }]
    };

    pieChart.setOption(option);

    window.addEventListener('resize', function () {
        pieChart.resize();
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const dom = document.getElementById('monthlyStatusChart');
    const yearSelect = document.getElementById('monthlyStatusYear');
    if (!dom) return;

    const old = echarts.getInstanceByDom(dom);
    if (old) old.dispose();

    const chart = echarts.init(dom);
    const url = "{{ route('admin.report.monthly-status') }}";

    const fullMonths  = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const shortMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    // Start with data already on the page, then refresh via fetch
    let state = (window.adminReportData && window.adminReportData.monthlyStatus) || {};

    const series = (name, data, color, barWidth, top = false) => ({
        name,
        type: 'bar',
        stack: 'status',
        data: data || new Array(12).fill(0),
        barMaxWidth: barWidth,
        itemStyle: { color, borderRadius: top ? [4, 4, 0, 0] : 0 },
        emphasis: { focus: 'series' }
    });

    function total(s) {
        return [].concat(s.approved || [], s.pending || [], s.rejected || [])
                 .reduce((a, b) => a + Number(b || 0), 0);
    }

    function buildOption() {
        const w = dom.clientWidth;
        const isSmall = w < 576;
        const isMedium = w >= 576 && w < 992;
        const empty = total(state) === 0;

        return {
            title: empty ? {
                text: 'No research records for ' + (state.year ?? ''),
                left: 'center',
                top: 'middle',
                textStyle: { fontSize: 14, fontWeight: 'normal', color: '#9ca3af' }
            } : { show: false },

            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, confine: true },

            legend: {
                bottom: 0, icon: 'roundRect',
                itemWidth: isSmall ? 10 : 14, itemHeight: isSmall ? 10 : 14,
                textStyle: { fontSize: isSmall ? 11 : 13 }
            },

            grid: {
                left: isSmall ? '2%' : '3%', right: isSmall ? '2%' : '3%',
                top: '6%', bottom: isSmall ? '16%' : '14%', containLabel: true
            },

            xAxis: {
                type: 'category',
                data: (isSmall || isMedium) ? shortMonths : fullMonths,
                axisTick: { show: false },
                axisLabel: { interval: 0, rotate: isSmall ? 45 : 0, fontSize: isSmall ? 10 : 12 }
            },

            yAxis: {
                type: 'value',
                min: 0,
                max: (v) => Math.max(v.max, 5),   // keeps the axis visible even when empty
                minInterval: 1,
                axisLabel: { fontSize: isSmall ? 10 : 12 },
                splitLine: { lineStyle: { type: 'dashed', color: '#e5e7eb' } }
            },

            series: [
                series('Approved', state.approved, '#0F9D8A', isSmall ? 14 : 28),
                series('Pending',  state.pending,  '#E8A93B', isSmall ? 14 : 28),
                series('Rejected', state.rejected, '#D9534F', isSmall ? 14 : 28, true)
            ]
        };
    }

    function fillYears() {
        const years = (state.years && state.years.length) ? state.years : [new Date().getFullYear()];
        yearSelect.innerHTML = years
            .map(y => `<option value="${y}" ${Number(y) === Number(state.year) ? 'selected' : ''}>${y}</option>`)
            .join('');
    }

    async function load(year) {
        try {
            const res = await fetch(`${url}?year=${year}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            state = await res.json();
            chart.setOption(buildOption(), true);
        } catch (e) {
            console.error('[monthly-status] failed to load', e);
        }
    }

    // Initial render
    fillYears();
    chart.setOption(buildOption(), true);

    // If the page-load data was empty or missing, try fetching the latest year with data
    if (!state.year || total(state) === 0) {
        const y = (state.years && state.years[0]) || new Date().getFullYear();
        load(y).then(fillYears);
    }

    yearSelect.addEventListener('change', () => load(yearSelect.value));

    // Responsive
    let t = null;
    const refit = () => {
        clearTimeout(t);
        t = setTimeout(() => { chart.resize(); chart.setOption(buildOption(), true); }, 100);
    };

    if ('ResizeObserver' in window) new ResizeObserver(refit).observe(dom);
    else window.addEventListener('resize', refit);

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab =>
        tab.addEventListener('shown.bs.tab', refit)
    );
});
</script>
@endsection
