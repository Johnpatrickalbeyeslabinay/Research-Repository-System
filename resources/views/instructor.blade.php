@extends('layouts.instructor_layout')

@section('title', 'Instructor')

@section('instructor')

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                Research Review Dashboard
            </h3>

            <p class="text-secondary mb-0">
                Review, approve, and provide feedback on student research submissions
            </p>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row g-4">

            <!-- Total Submissions -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-2">Total Submissions</p>
                            <h3 class="fw-normal mb-0">{{ $studFiles->count() }}</h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                            <i class="fa-regular fa-file-lines text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-2">Approved</p>
                            <h3 class="fw-normal mb-0">2</h3>
                        </div>

                        <div class="bg-success bg-opacity-10 rounded-2 p-3">
                            <i class="fa-regular fa-circle-check text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-2">Pending Review</p>
                            <h3 class="fw-normal mb-0">1</h3>
                        </div>

                        <div class="bg-warning bg-opacity-10 rounded-2 p-3">
                            <i class="fa-regular fa-clock text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
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

                            <!-- Approved -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Approved</span>
                                <strong>3 (38%)</strong>
                            </div>

                            <div class="progress mb-4" style="height:8px;">
                                <div class="progress-bar bg-success" style="width:38%"></div>
                            </div>

                            <!-- Pending -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pending Review</span>
                                <strong>2 (25%)</strong>
                            </div>

                            <div class="progress mb-4" style="height:8px;">
                                <div class="progress-bar bg-warning" style="width:25%"></div>
                            </div>

                            <!-- Under Revision -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Under Revision</span>
                                <strong>2 (25%)</strong>
                            </div>

                            <div class="progress mb-4" style="height:8px;">
                                <div class="progress-bar bg-primary" style="width:25%"></div>
                            </div>

                            <!-- Rejected -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Rejected</span>
                                <strong>1 (13%)</strong>
                            </div>

                            <div class="progress" style="height:8px;">
                                <div class="progress-bar bg-danger" style="width:13%"></div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="col-12 col-lg-6">

                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-y: auto;">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center mb-4">
                                <i class="bi bi-graph-up-arrow text-success fs-5 me-2"></i>
                                <h5 class="mb-0">
                                    Recent Actions Needed
                                </h5>
                            </div>

                            @foreach($studFiles as $file)

                                <div class="row align-items-center gy-3 py-3 border-bottom">

                                    <div class="col">
                                        <h6 class="fw-semibold mb-1 text-truncate">
                                            {{ $file->title }}
                                        </h6>

                                        <small class="text-secondary">
                                            {{ $file->name }}
                                        </small>
                                    </div>

                                    <div class="col-auto">
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                            Review
                                        </button>
                                    </div>

                                </div>

                            @endforeach

                        </div>
                    </div>

                </div>

            </div>
        </div>


        <!-- ================= REVIEW ================= -->
        <div class="tab-pane fade" id="review">

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

                                <button class="rounded py-1 mt-3 bg-white text-primary"
                                    style="max-width:200px;width:100%; border: 1px solid blue;"
                                    popovertarget="popover-{{ $file->id }}">

                                    <i class="bi bi-eye me-2"></i>
                                    Review & Comment

                                </button>

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

                                            <!-- Feedback Type -->
                                            <div class="mb-3">
                                                <select class="form-select form-select-sm">
                                                    <option selected>General Feedback</option>
                                                    <option>Request Revision</option>
                                                    <option>Approval Comment</option>
                                                </select>
                                            </div>

                                            <!-- Comment -->
                                            <div class="mb-3">
                                                <textarea class="form-control form-control-sm" rows="4"
                                                    placeholder="Enter your feedback, comments, or revision instructions..."></textarea>
                                            </div>

                                            <!-- Button -->
                                            <button type="submit" class="rounded py-1 mt-3 text-white border-0"
                                                style="max-width:200px; width:100%; background: #072a89;">

                                                <i class="bi bi-chat-left me-2"></i>
                                                Post Feedback
                                            </button>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>





@endsection
