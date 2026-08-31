@extends('layouts.student_layout')

@section('title', 'Student')

@section('student')


    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif



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


           <button class="rounded-2 px-2 py-1 bg-white border border-secondary"
                    popovertarget="account">
                Manage Account
            </button>

            <button class="rounded-2 px-2 py-1 text-white border-0" style="background: #072a89;" popovertarget="popover">
                <i class=" fa-solid fa-upload me-2"></i>
                Upload New Research
            </button>
        </div>
    </div>

    <div class="container-fluid py-4">

        <div class="row g-4">

        <!--MANAGE ACCOUNT BTN-->
        <div id="account" popover class="rounded-4 border-0 shadow p-4"
                style="width: 520px; max-width: 95vw; min-height: 500px;">


                <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold">Manage Account</h5>
                <button class="btn-close" popovertarget="account" popovertargetaction="hide"></button>
                </div>

                <div class="bg-info-soft rounded-3 p-3 mb-4">
                <p class="mb-1 text-info-strong"><strong>Name:</strong> {{ Auth::user()->name }}</p>
                <p class="mb-1 text-info-strong"><strong>User ID:</strong> {{ Auth::user()->user_id }}</p>
                <p class="mb-0 text-info-strong"><strong>Role:</strong> {{ Auth::user()->role }}</p>
                </div>

                <h6 class="fw-bold mb-3">Change Password</h6>

                <form>
                <div class="mb-3">
                    <label for="currentPassword" class="form-label fw-medium">Current Password</label>
                    <div class="input-group">
                    <input type="password" class="rounded-2 form-control" id="currentPassword" placeholder="Enter current password">
                    <button type="button" class=" toggle-show" onclick="togglePassword('currentPassword', this)">Show</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="newPassword" class="form-label fw-medium">New Password</label>
                    <div class="input-group">
                    <input type="password" class="rounded-2 form-control" id="newPassword" placeholder="At least 8 characters">
                    <button type="button" class=" toggle-show" onclick="togglePassword('newPassword', this)">Show</button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirmPassword" class="form-label fw-medium">Confirm New Password</label>
                    <div class="input-group">
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Repeat new password">
                    <button type="button" class="toggle-show" onclick="togglePassword('confirmPassword', this)">Show</button>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="text-white rounded-3 border-0 flex-grow-1 fw-bold py-2" style="background: #072a89;">Save New Password</button>
                    <button type="button" class="btn btn-outline-secondary" popovertarget="account"
                            popovertargetaction="hide">
                            Cancel
                        </button>
                </div>
                </form>

        </div>






        <!--Uploadfikes-->
            <div id="popover" popover class="rounded-4 border-0 shadow p-4"
                style="width: 520px; max-width: 95vw; min-height: 580px;">


                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Submit New Research</h3>

                    <button class="btn-close" popovertarget="popover" popovertargetaction="hide"></button>
                </div>

                <form action="{{ route('student.store') }}" method="POST" enctype="multipart/form-data">

                    @csrf

                    <!-- Research Title -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Research Title <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="title" class="form-control form-control-lg"
                            placeholder="Enter the title of your research">
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                Research Adviser <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="adviser" class="form-control" placeholder="e.g., Dr. Maria Santos">
                        </div>
                    </div>

                    <!-- Abstract -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Abstract <span class="text-danger">*</span>
                        </label>

                        <textarea class="form-control" name="desc" rows="4"
                            placeholder="Provide a comprehensive abstract"></textarea>
                    </div>

                    <!--visibility-->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Research Visibility *</label>

                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="visibility"
                                    id="public" value="public">

                                <label class="visibility-option border border-1 border-success rounded-3 w-100 p-2 text-dark" for="public">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-center">
                                            <i class="fa-solid fa-globe fa-lg"></i>
                                        </div>
                                        <div>
                                            <strong>Public</strong><br>
                                            <small class="text-muted">Visible in repository</small>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="col-6">
                                <input type="radio" class="btn-check" name="visibility"
                                    id="private" value="private">

                                <label class="visibility-private border border-1 border-secondary rounded-3 w-100 p-2" for="private">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-center">
                                            <i class="fa-solid fa-lock fa-lg"></i>
                                        </div>
                                        <div>
                                            <strong>Private</strong><br>
                                            <small class="text-muted">Only you, adviser & admins</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>


                    <!-- Upload -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold">
                            Upload PDF Document <span class="text-danger">*</span>
                        </label>

                        <input type="file" id="pdfFile" name="file" class="form-control" accept=".pdf">
                    </div>

                    <small class="text-secondary">
                        Accepted formats: PDF
                    </small>

                    <!-- Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <button class="rounded-2 p-2 text-white border-0" style="background: #072a89;">
                            Submit Research
                        </button>

                        <button type="button" class="btn btn-outline-secondary" popovertarget="popover"
                            popovertargetaction="hide">
                            Cancel
                        </button>
                    </div>

                </form>

            </div>



            <!-- Total Submissions -->
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-secondary mb-2">
                                Total Submissions
                            </p>

                            <h3 class="fw-normal mb-0">
                                {{ $totalUploads }}
                            </h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 rounded-2 p-1">
                            <i class="fa-regular fa-file-lines text-primary"></i>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Approved -->
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-secondary mb-2">
                                Approved
                            </p>

                            <h3 class="fw-normal mb-0">
                               {{ $approvedUploads }}
                            </h3>
                        </div>

                        <div class="bg-success bg-opacity-10 rounded-2 p-1">
                            <i class="fa-regular fa-circle-check text-success"></i>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Pending -->
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-secondary mb-2">
                                Pending
                            </p>

                            <h3 class="fw-normal mb-0">
                               {{ $pendingUploads }}
                            </h3>
                        </div>

                        <div class="bg-warning bg-opacity-10 rounded-2 p-1">
                            <i class="fa-regular fa-clock text-warning"></i>
                        </div>

                    </div>

                </div>

            </div>



            <!-- Rejected -->
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-secondary mb-2">
                                Rejected
                            </p>

                            <h3 class="fw-normal mb-0">
                               {{ $rejectedUploads }}
                            </h3>
                        </div>

                        <div class="bg-warning bg-opacity-10 rounded-2 p-1">
                            <i class="fa-regular fa-clock text-warning"></i>
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

        <!-- Notifications -->
        <button class="nav-link rounded-5 px-2 py-1  d-flex align-items-center gap-2" id="notification-tab"
            data-bs-toggle="pill" data-bs-target="#notification" type="button" role="tab">

            Notifications

            <span class="badge d-flex align-items-center justify-content-center text-danger">
                <i class="fa-solid fa-comment-dots"></i>
            </span>

        </button>

        <!-- Bookmarks -->
        <button class="nav-link rounded-5 px-2 py-1" id="bookmark-tab" data-bs-toggle="pill" data-bs-target="#bookmark"
            type="button" role="tab">
            Bookmarks
        </button>

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

                                <button class="view rounded px-2 py-1 mt-3 bg-white"
                                    style="max-width:200px;width:100%; border: 1px solid #5FEFDB; color: #0E7F77;"
                                    popovertarget="popover-{{ $file->id }}">

                                    <i class="bi bi-eye me-2"></i>
                                    View in Repository

                                </button>

                                <button class="view rounded px-2 py-1 mt-3 bg-white update-btn "
                                    popovertarget="update-sub-{{ $file->id }}">
                                    <i class="fa-regular fa-pen-to-square me-2"></i>
                                    Update Submission
                                </button>

                            </div>

                        </div>

                    </div>




                    <!--POPOVER-UPDATE-->
                    <div id="update-sub-{{ $file->id }}" popover class="rounded-4 border-0 shadow p-4"
                        style="width: 520px; max-width: 95vw; min-height: 580px;">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold mb-0">Update Research</h3>

                            <button class="btn-close" popovertarget="update-sub" popovertargetaction="hide"></button>
                        </div>

                        <form action="{{ route('student.update', $file->id) }}" method="POST" enctype="multipart/form-data">

                            @csrf
                            @method('PUT')


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
                                    <label class="form-label fw-semibold">
                                        Research Adviser <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="adviser"
                                        class="form-control"
                                        value="{{ old('adviser', $file->adviser) }}"
                                        placeholder="e.g., Dr. Maria Santos"
                                        required>
                                </div>
                            </div>

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
                                    required>{{ old('desc', $file->desc) }}</textarea>
                            </div>

                            <!--visibility-->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Research Visibility *</label>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <input
                                            type="radio"
                                            class="btn-check"
                                            name="visibility"
                                            id="public-{{ $file->id }}"
                                            value="public"
                                            {{ $file->visibility == 'public' ? 'checked' : '' }}>

                                        <label
                                            class="visibility-option border border-1 border-success rounded-3 w-100 p-2 text-dark"
                                            for="public-{{ $file->id }}">

                                            <div class="d-flex align-items-center gap-2">
                                                <div class="text-center">
                                                    <i class="fa-solid fa-globe fa-lg"></i>
                                                </div>

                                                <div>
                                                    <strong>Public</strong><br>
                                                    <small class="text-muted">Visible in repository</small>
                                                </div>
                                            </div>

                                        </label>
                                    </div>

                                    <div class="col-6">
                                        <input
                                            type="radio"
                                            class="btn-check"
                                            name="visibility"
                                            id="private-{{ $file->id }}"
                                            value="private"
                                            {{ $file->visibility == 'private' ? 'checked' : '' }}>

                                        <label
                                            class="visibility-private border border-1 border-secondary rounded-3 w-100 p-2"
                                            for="private-{{ $file->id }}">

                                            <div class="d-flex align-items-center gap-2">
                                                <div class="text-center">
                                                    <i class="fa-solid fa-lock fa-lg"></i>
                                                </div>

                                                <div>
                                                    <strong>Private</strong><br>
                                                    <small class="text-muted">Only you, adviser & admins</small>
                                                </div>
                                            </div>

                                        </label>
                                    </div>
                                </div>
                            </div>


                            <!-- Upload -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold">
                                    Upload PDF Document <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="file"
                                    id="pdfFile-{{ $file->id }}"
                                    name="file"
                                    class="form-control"
                                    accept=".pdf">
                            </div>

                            <small class="text-secondary">
                                Leave blank if you don't want to replace the current PDF.
                            </small>

                            <!-- Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button class="rounded-2 p-2 text-white border-0" style="background: #072a89;">
                                    Update Research
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

                                    <div class="text-muted small mb-4"> <span class="me-3">
                                            <span> <i class="bi bi-download"></i>
                                                {{ $file->downloads ?? 0 }} </span>
                                    </div>

                                    <div class="row g-4 mb-4">

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded-2 py-2 px-1 d-flex justify-content-center align-items-center"
                                                    style="width:45px; height:45px; background-color: #EFF6FF;">
                                                    <i class="fa-regular fa-user text-primary"></i>
                                                </div>

                                                <div>
                                                    <strong>Author</strong><br>
                                                    {{ $file->name }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-success bg-opacity-10 rounded-2 py-2 px-1 d-flex justify-content-center align-items-center"
                                                    style="width:45px; height:45px;">
                                                    <i class="fa-regular fa-calendar text-success"></i>
                                                </div>

                                                <div>
                                                    <strong>Year Published</strong><br>
                                                    {{ $file->created_at->format('Y') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded-2 py-2 px-1 d-flex justify-content-center align-items-center"
                                                    style="width:45px; height:45px;">
                                                    <i class="fa-regular fa-calendar-check text-primary"></i>
                                                </div>

                                                <div>
                                                    <strong>Submitted</strong><br>
                                                    {{ $file->created_at->format('M j, Y') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-success bg-opacity-10 rounded-2 py-2 px-1 d-flex justify-content-center align-items-center"
                                                    style="width:45px; height:45px;">
                                                    <i class="fa-solid fa-tags text-success"></i>
                                                </div>

                                                <div>
                                                    <strong>Keywords</strong><br>
                                                    {{ count(array_filter(explode(',', $file->auto_tag))) }} tags
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <h6 class="fw-bold">Abstract</h6>
                                    <p class="text-muted" style="text-align: justify;">
                                        {{ $file->desc ?? 'No abstract available.' }}
                                    </p>

                                    <h6 class="fw-bold mt-4">Keywords</h6>
                                    @foreach(explode(',', $file->auto_tag) as $tag)
                                        <span class="small rounded me-1 mb-1 px-2 py-1"
                                            style="background-color:#EFF6FF; color:#3B5FBF;">
                                            {{ trim($tag) }}
                                        </span>
                                    @endforeach

                                    <div
                                        class="alert alert-primary d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                        <div>
                                            <div>
                                                <div class="fw-bold">Full Research Document</div>
                                                <small>{{ basename($file->file_path) }}</small>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3 mt-md-0">

                                           <!--<a href="{{ route('file.view', $file->id) }}" target="_blank"
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>-->

                                            <a href="{{ route('file.view', ['id' => $file->id, 'v' => time()]) }}" target="_blank"
                                                class="btn btn-outline-primary px-2 py-1">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>

                                            <a href="{{ route('file.download', $file->id) }}"
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
    <script>
        document.getElementById('pdfFile').addEventListener('change', function () {
            const file = this.files[0];

            if (!file) return;

            const allowedTypes = ['application/pdf'];

            if (!allowedTypes.includes(file.type)) {
                alert('Only PDF files are allowed. DOC and DOCX files are not accepted.');
                this.value = '';
            }
        });
    </script>


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





@endsection
