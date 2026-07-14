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
            <button class="rounded-2 p-2 text-white border-0" style="background: #072a89;" popovertarget="popover">
                <i class=" fa-solid fa-upload me-2"></i>
                Upload New Research
            </button>
        </div>
    </div>

    <div class="container-fluid py-4">

        <div class="row g-4">

            <!-- Total Submissions -->
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-secondary mb-2">
                                Total Submissions
                            </p>

                            <h3 class="fw-normal mb-0">
                                {{ count($studFiles) }}
                            </h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 rounded-2 p-1">
                            <i class="fa-regular fa-file-lines text-primary"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div id="popover" popover class="rounded-4 border-0 shadow p-4"
                style="width: 520px; max-width: 95vw; min-height: 580px;">

                <!-- Header -->
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
                            placeholder="Provide a comprehensive abstract (minimum 100 words)"></textarea>
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

            <!-- Approved -->
            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-secondary mb-2">
                                Approved
                            </p>

                            <h3 class="fw-normal mb-0">
                                2
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
                                Pending Review
                            </p>

                            <h3 class="fw-normal mb-0">
                                1
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

                                <button class="view rounded py-1 mt-3 bg-white"
                                    style="max-width:200px;width:100%; border: 1px solid #5FEFDB; color: #0E7F77;"
                                    popovertarget="popover-{{ $file->id }}">

                                    <i class="bi bi-folder me-2"></i>
                                    View in Repository

                                </button>

                            </div>

                        </div>

                    </div>


                    <!-- POPOVER -->
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
                                                    style="width:45px; height:45px background-color: #EFF6FF;">
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
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>


                                            <a href="{{ route('file.download', $file->id) }}" class="btn btn-primary">
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

                    <h5 class="fw-bold">
                        Notifications
                    </h5>

                    @forelse($notifications as $notification)

                        <div class="border-bottom py-3">

                            <div class="fw-semibold text-success">
                                ✅ Research Submitted
                            </div>

                            <div>
                                You submitted
                                <strong>{{ $notification->title }}</strong>.
                            </div>

                            <small class="text-muted">
                                {{ $notification->created_at->diffForHumans() }}
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

                    <p class="text-secondary mb-0">
                        This is a bookmark message.
                    </p>

                </div>

            </div>

        </div>

    </div>

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
@endsection
