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
    <div class="bg-white rounded-4 p-2 shadow-sm d-inline-flex gap-2 nav nav-pills" id="dashboardTab" role="tablist">

        <!-- Overview -->
        <button class="nav-link active rounded-3 px-4" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview"
            type="button" role="tab">
            Overview
        </button>

        <!-- Review -->
        <button class="nav-link rounded-3 px-4 d-flex align-items-center gap-2" id="review-tab" data-bs-toggle="pill"
            data-bs-target="#review" type="button" role="tab">

            Review Research

            <span class="badge bg-warning rounded-circle d-flex align-items-center justify-content-center"
                style="width:20px;height:20px;font-size:11px;">
                {{ $studFiles->count() }}
            </span>

        </button>

        <!-- My Students -->
        <button class="nav-link rounded-3 px-4" id="student-tab" data-bs-toggle="pill" data-bs-target="#student"
            type="button" role="tab">
            My Students
        </button>

    </div>


    <div class="tab-content">

        <!-- ================= OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview">

            <div class="row g-3 mt-3">

                <div class="container py-5">

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">

                            <!-- Header -->
                            <div class="d-flex align-items-center mb-4">
                                <i class="bi bi-graph-up-arrow text-success fs-5 me-2"></i>
                                <h5 class=" mb-0">
                                    <i class="fa-solid fa-arrow-trend-up text-success"></i> Recent Actions Needed
                                </h5>
                            </div>

                            <!-- Item 1 -->
                            @foreach($studFiles as $file)
                                <div class="row align-items-center gy-3 py-3 border-bottom">

                                    <div class="col-12 col-lg">
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

            <div class="mt-3 ">

                <div class="card-body">

                    @foreach($studFiles as $file)

                        <div class="col-12">

                            <div class="card mt-3 border-0 shadow-sm rounded-4">

                                <div class="card-body">

                                    <div class="row align-items-center">

                                        <div class="col-lg-9">

                                            <h4 class="fw-bold mb-2">
                                                {{ $file->title }}
                                            </h4>

                                            <div class="d-flex flex-wrap gap-3 text-muted small mb-3">

                                                <span>
                                                    Adviser:
                                                    <i class="bi bi-person-lines-fill me-1"></i>
                                                    {{ $file->adviser }}
                                                </span>

                                                <span>
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ $file->created_at->format('M d, Y') }}
                                                </span>

                                                <span>
                                                    <i class="fa-regular fa-eye me-1"></i>
                                                    {{ $file->views ?? 0 }}
                                                </span>

                                                <span>
                                                    <i class="fa-solid fa-download me-1"></i>
                                                    {{ $file->downloads ?? 0 }}
                                                </span>

                                            </div>

                                            <p class="text-secondary mb-3">
                                                {{ \Illuminate\Support\Str::limit($file->desc, 180) }}
                                            </p>

                                            <span
                                                class="badge bg-info text-dark rounded-pill d-inline-block  w-md-auto text-wrap"
                                                style="white-space: normal;">
                                                {{ $file->auto_tag }}
                                            </span>

                                        </div>

                                    </div>

                                    <button class="btn btn-outline-primary rounded mt-3" style="max-width:200px;width:100%;"
                                        popovertarget="popover-{{ $file->id }}">

                                        <i class="bi bi-eye me-2"></i>
                                        View in Repository

                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- POPOVER -->
                        <div id="popover-{{ $file->id }}" popover
                            style="border: 1px solid #dee2e6;  max-width: 1200px; width: 90%;">
                            <div class="container py-5">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-4 p-md-5">
                                        <h2 class="fw-bold mb-2"> {{ $file->title }} </h2>
                                        <div class="text-muted small mb-4"> <span class="me-3"> <i class="bi bi-eye"></i>
                                                {{ $file->views ?? 0 }} </span> <span> <i class="bi bi-download"></i>
                                                {{ $file->downloads ?? 0 }} </span> </div>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-3 col-6"> <strong>Author</strong><br> {{ $file->name }} </div>
                                            <div class="col-md-3 col-6"> <strong>Adviser</strong><br> {{ $file->adviser }}
                                            </div>
                                            <div class="col-md-3 col-6"> <strong>Year Published</strong><br>
                                                {{ $file->created_at->format('Y') }} </div>
                                            <div class="col-md-3 col-6"> <strong>Submitted</strong><br>
                                                {{ $file->created_at->format('M j, Y') }} </div>
                                            <div class="col-md-3 col-6"> <strong>Keyword</strong><br> {{ $file->auto_tag }}
                                            </div>
                                        </div>
                                        <h5 class="fw-bold">Abstract</h5>
                                        <p class="text-muted"> {{ $file->desc ?? 'No abstract available.' }} </p>
                                        <h5 class="fw-bold mt-4">Keywords</h5> <span class="badge bg-secondary">
                                            {{ $file->auto_tag }} </span>
                                        <div
                                            class="alert alert-primary d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                            <div>
                                                <div>
                                                    <div class="fw-bold">Full Research Document</div>
                                                    <small>{{ basename($file->file_path) }}</small>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2 mt-3 mt-md-0">

                                                <a href="{{ asset('storage/' . $file->file_path) }}" download
                                                    class="btn btn-primary">
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

        </div>



        <!-- ================= MY STUDENTS ================= -->
        <div class="tab-pane fade" id="student">

            <div class="mt-3 ">

                <div class="card-body">




                </div>

            </div>

        </div>
    </div>





@endsection