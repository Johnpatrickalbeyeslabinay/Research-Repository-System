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

            <!-- Total Views -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-2">Total Views</p>
                            <h3 class="fw-normal mb-0">1874</h3>
                        </div>

                        <div class="bg-danger bg-opacity-10 rounded-2 p-3">
                            <i class="fa-regular fa-eye text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- NAV BUTTONS -->
    <div class="bg-white rounded-4 p-2 shadow-sm d-inline-flex gap-2 nav nav-pills" id="dashboardTab" role="tablist">

        <!-- My Submissions -->
        <button class="nav-link active rounded-3 px-4" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview"
            type="button" role="tab">
            Overview
        </button>

        <!-- Notifications -->
        <button class="nav-link rounded-3 px-4 d-flex align-items-center gap-2" id="review-tab" data-bs-toggle="pill"
            data-bs-target="#review" type="button" role="tab">

            Review Research

            <span class="badge bg-warning rounded-circle d-flex align-items-center justify-content-center"
                style="width:20px;height:20px;font-size:11px;">
                {{ $studFiles->count() }}
            </span>

        </button>

        <!-- Bookmarks -->
        <button class="nav-link rounded-3 px-4" id="student-tab" data-bs-toggle="pill" data-bs-target="#student"
            type="button" role="tab">
            My Students
        </button>

    </div>


    <div class="tab-content">

        <!-- ================= OVERVIEW ================= -->
        <div class="tab-pane fade show active" id="overview">

            <div class="row g-3 mt-3">
                <h1>Overview Tab</h1>
            </div>
        </div>

        <!-- ================= NOTIFICATIONS ================= -->
        <div class="tab-pane fade" id="review">

            <div class="card mt-3 shadow-sm border-0 rounded-4">

                <div class="card-body">

                    <h5 class="fw-bold">
                        Review Research
                    </h5>

                    <p class="text-secondary mb-0">
                        This is a review research.
                    </p>

                </div>

            </div>

        </div>

        <!-- ================= MY STUDENTS ================= -->
        <div class="tab-pane fade" id="student">

            <div class="card mt-3 shadow-sm border-0 rounded-4">

                <div class="card-body">

                    <h5 class="fw-bold">
                        My Students
                    </h5>

                    <p class="text-secondary mb-0">
                        {{ $studFiles->count() }} students have submitted research for review.
                    </p>

                </div>

            </div>

        </div>

    </div>

@endsection