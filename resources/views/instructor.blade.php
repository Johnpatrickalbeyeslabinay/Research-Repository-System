@extends('layouts.instructor_layout')

@section('title', 'Instructor')

@section('instructor')

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                Research Review Dashboard
            </h3>

            <small class="text-secondary mb-0">
                Review, approve, and provide feedback on student research submissions
            </small>
        </div>

    </div>

    <!-- Dashboard Statistics -->
<div class="row flex-nowrap overflow-auto g-3 pb-2">

    <!-- Total Submissions -->
    <div class="col-10 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">
                        Total Submissions
                    </p>

                    <h3 class="fw-normal mb-0">
                        {{ $studFiles->count() }}
                    </h3>
                </div>

                <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                    <i class="fa-regular fa-file-lines text-primary fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Approved -->
    <div class="col-10 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">
                        Approved
                    </p>

                    <h3 class="fw-normal mb-0">
                        {{ $approvedCount }}
                    </h3>
                </div>

                <div class="bg-success bg-opacity-10 rounded-2 p-3">
                    <i class="fa-regular fa-circle-check text-success fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Pending -->
    <div class="col-10 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">
                        Pending Review
                    </p>

                    <h3 class="fw-normal mb-0">
                        {{ $pendingCount }}
                    </h3>
                </div>

                <div class="bg-warning bg-opacity-10 rounded-2 p-3">
                    <i class="fa-regular fa-clock text-warning fs-4"></i>
                </div>

            </div>
        </div>
    </div>


    <!-- Rejected -->
    <div class="col-10 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <p class="text-secondary mb-2">
                        Rejected
                    </p>

                    <h3 class="fw-normal mb-0">
                        {{ $rejectedCount }}
                    </h3>
                </div>

                <div class="bg-danger bg-opacity-10 rounded-2 p-3">
                    <i class="fa-regular fa-circle-xmark text-danger fs-4"></i>
                </div>

            </div>
        </div>
    </div>

</div>





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


    <!-- NAV BUTTONS -->
    <div class="bg-white rounded-5 shadow-sm d-inline-flex gap-2 nav nav-pills" id="dashboardTab" role="tablist">

        <!-- My Submissions -->
        <button class="nav-link active rounded-5 px-2 py-1" id="overview-tab" data-bs-toggle="pill"
            data-bs-target="#overview" type="button" role="tab">
            Overview
        </button>

        <!-- Review -->
        <button class="nav-link rounded-5 px-1 py-1  d-flex align-items-center gap-2" id="review-tab" data-bs-toggle="pill"
            data-bs-target="#review" type="button" role="tab">

            Review Research

            <span class="badge d-flex align-items-center justify-content-center text-danger">
                <i class="fa-solid fa-circle fa-2xs text-danger"></i>
            </span>

        </button>


        <!-- Notification -->
        <button class="nav-link rounded-5 px-1 py-1  d-flex align-items-center gap-2" id="notification-tab" data-bs-toggle="pill"
            data-bs-target="#notification" type="button" role="tab">

            Notification

            <span class="badge d-flex align-items-center justify-content-center text-dark">
                <i class="fa-regular fa-bell"></i>
            </span>

        </button>

    </div>


    <div class="tab-content">

        <!-- ================= OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview">

            <div class="row g-4 mt-3">

               <!-- Graph -->
                        <div class="col-12 col-lg-6">

                            <div class="card shadow-sm rounded-4 border-0 h-100">

                                <div class="card-body p-4">

                                    <h5 class="mb-4">
                                        <i class="bi bi-bar-chart-line me-2 text-primary"></i>
                                        Submission Status Summary
                                    </h5>

                                    @php
                                        $totalSubmissions = $approvedCount + $pendingCount + $rejectedCount;

                                        $approvedPercentage = $totalSubmissions > 0
                                            ? round(($approvedCount / $totalSubmissions) * 100)
                                            : 0;

                                        $pendingPercentage = $totalSubmissions > 0
                                            ? round(($pendingCount / $totalSubmissions) * 100)
                                            : 0;

                                        $rejectedPercentage = $totalSubmissions > 0
                                            ? round(($rejectedCount / $totalSubmissions) * 100)
                                            : 0;
                                    @endphp


                                    <!-- APPROVED -->
                                    <div class="d-flex justify-content-between mb-2">

                                        <span>
                                            Approved
                                        </span>

                                        <strong>
                                            {{ $approvedCount }} ({{ $approvedPercentage }}%)
                                        </strong>

                                    </div>

                                    <div class="progress mb-4" style="height: 8px;">

                                        <div class="progress-bar bg-success"
                                            style="width: {{ $approvedPercentage }}%">
                                        </div>

                                    </div>


                                    <!-- PENDING -->
                                    <div class="d-flex justify-content-between mb-2">

                                        <span>
                                            Pending Review
                                        </span>

                                        <strong>
                                            {{ $pendingCount }} ({{ $pendingPercentage }}%)
                                        </strong>

                                    </div>

                                    <div class="progress mb-4" style="height: 8px;">

                                        <div class="progress-bar bg-warning"
                                            style="width: {{ $pendingPercentage }}%">
                                        </div>

                                    </div>


                                    <!-- REJECTED -->
                                    <div class="d-flex justify-content-between mb-2">

                                        <span>
                                            Rejected
                                        </span>

                                        <strong>
                                            {{ $rejectedCount }} ({{ $rejectedPercentage }}%)
                                        </strong>

                                    </div>

                                    <div class="progress" style="height: 8px;">

                                        <div class="progress-bar bg-danger"
                                            style="width: {{ $rejectedPercentage }}%">
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                                <!-- ACTIONS -->
                                <div class="col-12 col-lg-6">

                                    <div class="card border-0 shadow-sm rounded-4 h-100">

                                        <div class="card-body p-3 p-md-4">

                                            <div class="d-flex align-items-center mb-4">
                                                <i class="bi bi-graph-up-arrow text-success fs-5 me-2"></i>
                                                <h5 class="mb-0">Recent Actions Needed</h5>
                                            </div>

                                            <div class="action-list"
                                                style="max-height: 340px; overflow-y: auto;">

                                                @forelse($pendingFiles as $file)

                                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 gap-sm-3 py-3 border-bottom">

                                                        <!-- TITLE + NAME -->
                                                        <div class="flex-grow-1" style="min-width: 0;">

                                                            <strong class="fw-semibold small d-block text-truncate text-dark"
                                                                title="{{ $file->title }}">

                                                                {{ \Illuminate\Support\Str::limit($file->title, 45, '...') }}

                                                            </strong>

                                                            <small class="text-secondary d-block text-truncate">
                                                                {{ implode(', ', $file->researcherNames()) }}
                                                            </small>

                                                        </div>

                                                        <!-- STATUS + REVIEW -->
                                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">

                                                            <span class="badge text-dark rounded-pill px-2 py-1"
                                                                style="background-color: #fff3cd;">
                                                                Pending
                                                            </span>

                                                            <button
                                                                class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-1"
                                                                id="review-tab" data-bs-target="#review" type="button" role="tab">

                                                                <i class="bi bi-eye me-1"></i>
                                                                Review

                                                            </button>

                                                        </div>

                                                    </div>

                                                @empty

                                                    <div class="text-center text-secondary py-5">

                                                        <i class="bi bi-check2-circle fs-2 d-block mb-2 text-success"></i>

                                                        <p class="mb-0 small">
                                                            No pending actions at this time.
                                                        </p>

                                                    </div>

                                                @endforelse

                                            </div>

                                        </div>

                                    </div>

                                </div>

                        </div>
                    </div>


        <!-- ================= REVIEW ================= -->
        <div class="tab-pane fade" id="review">

            <div class="row g-3 mt-3">

                @foreach($studFiles as $file)

                {{-- $studFiles is already scoped to this adviser by adviser_id in
                     InstructorController::index() — this used to re-filter by a
                     string comparison ($file->adviser == Auth::user()->name)
                     instead, which silently blanked this whole tab for any
                     adviser whose display name had since changed. Kept as a
                     defensive check, now against the actual FK. --}}
                @if($file->adviser_id === Auth::id())

                    <!-- CARD -->
                    <div class="col-12">

                        <div class="card border-1 shadow-sm rounded-4">

                            <div class="card-body">

                                <div class="row align-items-center">

                                    <div class="col-lg-9">


                                    <!-- STATUS & VISIBILITY -->
                                    <div class="position-absolute top-0 end-0 m-3 d-flex align-items-center gap-2">

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

                                    </div>

                                        <h5 class="fw-bold mb-2">
                                            {{ $file->title }}
                                        </h5>

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

                                        <p class="text-secondary mb-3">
                                            {{ \Illuminate\Support\Str::limit($file->desc, 180) }}
                                        </p>

                                        @foreach(explode(',', $file->auto_tag) as $tag)
                                            <span class="small rounded me-1 mb-1 px-2 py-1"
                                                style="background-color:#EFF6FF; color:#3B5FBF;">
                                                {{ trim($tag) }}
                                            </span>
                                        @endforeach
                                    </div>

                                </div>

                               <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">

                                    <!-- REVIEW & COMMENT -->
                                    <button class="rounded px-2 py-1 bg-white text-primary"
                                        style="max-width:200px; width:100%; border:1px solid blue;"
                                        popovertarget="popover-{{ $file->id }}">

                                        <i class="bi bi-eye me-2"></i>
                                        Review & Comment

                                    </button>


                                    <!-- ACTION -->
                                    @if($file->status == 'pending')

                                        <form action="{{ route('instructor.research.approve', $file->id) }}"
                                            method="POST"
                                            class="m-0">

                                            @csrf

                                            <button type="submit" class="btn btn-outline-success px-2 py-1">
                                                <i class="fa-regular fa-circle-check me-1"></i>
                                                Approve
                                            </button>

                                        </form>


                                        <form action="{{ route('instructor.research.reject', $file->id) }}"
                                            method="POST"
                                            class="m-0">

                                            @csrf

                                            <button type="submit" class="btn btn-outline-danger px-2 py-1">
                                                <i class="fa-regular fa-circle-xmark me-1"></i>
                                                Reject
                                            </button>

                                        </form>

                                    @endif

                                </div>


                            </div>

                        </div>

                    </div>


                    <!-- POPOVER -->
                    <div id="popover-{{ $file->id }}" popover
                        style="border: 1px solid #dee2e6;  max-width: 1200px; width: 90%; overflow-y: auto; max-height: 90vh;">
                        <div class="container py-5">
                            <div class="border-0">

                                <div class="card-body p-4 p-md-5">

                                    <h4 class="mb-3">Review Research Paper</h4>

                                    <button class="btn-close position-absolute top-0 end-0 m-3"
                                        popovertarget="popover-{{ $file->id }}" popovertargetaction="hide">
                                    </button>


                                    <h5 class="fw-bold mb-2"> {{ $file->title }} </h5>

                                    {{-- Was: "Author" = $file->name only (every co-author invisible),
                                         "Year Published" = the upload date (not publication_year),
                                         "Keywords" = a count of auto-generated title words. This adviser
                                         is making an approve/reject decision on incomplete, sometimes
                                         wrong, information without it. --}}
                                    <x-research.meta :file="$file" />

                                    <div class="mb-4">
                                        <span class="text-muted">Submitted:</span>
                                        {{ $file->created_at->format('M j, Y') }}
                                    </div>

                                    <h6 class="fw-bold">Abstract</h6>
                                    <p class="text-muted" style="text-align: justify;">
                                        {{ $file->desc ?? 'No abstract available.' }}
                                    </p>

                                    <!--file-->
                                    <div
                                        class="border border-1 rounded p-2 d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                        <div>
                                            <div>
                                                <div class="fw-bold">Full Research Document</div>
                                                <small>{{ basename($file->file_path) }}</small>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3 mt-md-0">

                                            <a href="{{ route('file.view', ['id' => $file->id, 'v' => time()]) }}" target="_blank"
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>

                                        </div>

                                    </div>



                                    <div class="rounded-4">
                                        <div class="card-body p-4">

                                            <h5 class="fw-bold mb-4">
                                                Add Feedback / Comment
                                            </h5>

                                            <form action="{{ route('instructor.research.feedback', $file->id) }}" method="POST">
                                                @csrf

                                                <!-- Comment -->
                                                <div class="mb-3">
                                                    <textarea name="feedback" class="form-control form-control-sm" rows="4"
                                                        placeholder="Enter your feedback, comments, or revision instructions...">{{ old('feedback', $file->instructor_feedback) }}</textarea>
                                                </div>

                                                <!-- Button -->
                                                <button type="submit" class="rounded py-1 mt-3 text-white border-0"
                                                    style="max-width:200px; width:100%; background: #072a89;">

                                                    <i class="bi bi-chat-left me-2"></i>
                                                    Post Feedback
                                                </button>
                                            </form>


                                            @if($file->status == 'pending')

                                            <form action="{{ route('instructor.research.approve', $file->id) }}"
                                                method="POST"
                                                class="m-0">

                                                @csrf

                                                <button type="submit" class="btn btn-outline-success px-2 py-1">
                                                    <i class="fa-regular fa-circle-check me-1"></i>
                                                    Approve
                                                </button>

                                            </form>


                                            <form action="{{ route('instructor.research.reject', $file->id) }}"
                                                method="POST"
                                                class="m-0">

                                                @csrf

                                                <button type="submit" class="btn btn-outline-danger px-2 py-1">
                                                    <i class="fa-regular fa-circle-xmark me-1"></i>
                                                    Reject
                                                </button>

                                            </form>

                                        @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>





             <!-- ================= NOTIFICATION ================= -->
        <div class="tab-pane fade" id="notification">

            <div class="card mt-3 shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h5 class="fw-bold">Notifications</h5>

                    @forelse($notifications as $notification)
                        <div class="border-bottom py-3">
                            <div class="fw-semibold {{ $notification->read_at ? 'text-secondary' : 'text-primary' }}">
                                <i class="fa-solid fa-upload"></i>
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </div>

                            <small class="text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                    @empty
                        <p class="text-secondary">No notifications yet.</p>
                    @endforelse
                </div>
            </div>

        </div>


    </div>





@endsection
