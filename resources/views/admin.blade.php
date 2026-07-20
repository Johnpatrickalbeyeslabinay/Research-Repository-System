@extends('layouts.admin_layout')

@section('title', 'ADMIN')

@section('admin')


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

    <div class="row g-3">

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
                        <h4 class="fw-bold mb-0">{{ count($users) }}</h4>
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
                        <h4 class="fw-bold mb-0">{{ count($student) }}</h4>
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
                        <h4 class="fw-bold mb-0">{{ count($instructor) }}</h4>
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
                        <h4 class="fw-bold mb-0">{{ count($studFiles) }}</h4>
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



<!--btn ng mga buttons haha-->
<div class="tab-content">
 <div class="bg-white rounded-5 shadow-sm d-inline-flex gap-2 nav nav-pills mt-3" id="dashboardTab" role="tablist">

        <!-- My Submissions -->
        <button class="nav-link active rounded-5 px-2 py-1" id="overview-tab" data-bs-toggle="pill"
            data-bs-target="#overview" type="button" role="tab">
            Overview
        </button>

        <!-- Notifications -->
        <button class="nav-link rounded-5 px-2 py-1" id="submissions-tab"
            data-bs-toggle="pill" data-bs-target="#submissions" type="button" role="tab">

            My Submissions

        </button>

        <!-- Bookmarks -->
        <button class="nav-link rounded-5 px-2 py-1" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics"
            type="button" role="tab">
            Analytics
        </button>

        <button class="nav-link rounded-5 px-2 py-1" id="tracking-tab" data-bs-toggle="pill" data-bs-target="#tracking"
            type="button" role="tab">
            Monthly Tracking
        </button>

        <button class="nav-link rounded-5 px-2 py-1" id="audit-tab" data-bs-toggle="pill" data-bs-target="#audit"
            type="button" role="tab">
            Audit Logs
        </button>

    </div>



    <!--Content ng mga Buttons-->

 <!-- OVERVIEW -->
<div class="tab-pane fade show active" id="overview">

    <div class="mt-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Overview Contents</h3>
        </div>

        <div class="row g-4">

            <!-- Bar Graph -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">

                    <form method="GET">
                        <input type="hidden" name="view" value="dashboard">

                        <select name="year" class="form-select" onchange="this.form.submit()">
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </form>
                    <div class="card-body">
                        <div id="monthlyChart" style="width:100%; height:350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div id="pieChart" style="width:100%; height:350px;"></div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 mt-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div id="lineChart" style="width:100%; height:350px;"></div>
                </div>
            </div>
        </div>

    </div>

</div>


    <!--SUBMISSIONS BUTTONS-->

    <div class="tab-pane fade" id="submissions">

            <div class="container-fluid py-4">

    <div class="card rounded-5 shadow-sm border-0">

        <div class="card-body p-4">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

                <h3 class="fw-bold mb-3 mb-md-0">
                    All Research Submissions
                </h3>

                <div class="d-flex flex-wrap gap-3">

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

                </div>

            </div>

            <!-- Table -->
            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>Title</th>
                            <th>Author</th>
                            <th>Adviser</th>
                            <th>Submitted</th>
                            <th>Views</th>
                            <th>Downloads</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>

                        </tr>

                    </thead>

                    <tbody>
                        @foreach($studFiles as $file)

                        <tr>
                            <td style="max-width: 250px;">
                                <h6 class="fw-semibold mb-0 text-truncate">
                                    {{ $file->title }}
                                </h6>
                            </td>

                            <td>{{ $file->name }}</td>

                            <td class="text-muted">{{ $file->adviser }}</td>

                            <td> {{ $file->created_at->format('M d, Y') }}</td>

                            <td>

                                <span class="text-primary">

                                    <i class="bi bi-eye"></i>
                                    {{ $file->views ?? 0 }}
                                </span>

                            </td>

                            <td>

                                <span class="text-primary">

                                    <i class="bi bi-download"></i>
                                        {{ $file->downloads ?? 0 }}
                                </span>

                            </td>

                            <td>
                                <span class="rounded text-bg-success-subtle text-success px-3 py-2">
                                   @if(empty($file->status) || $file->status == 'pending')
                                        <span class="badge bg-warning text-dark rounded-pill">
                                            Pending
                                        </span>
                                    @elseif($file->status == 'approved')
                                        <span class="badge bg-success rounded-pill">
                                            Approved
                                        </span>
                                    @elseif($file->status == 'rejected')
                                        <span class="badge bg-danger rounded-pill">
                                            Rejected
                                        </span>
                                    @endif

                                </span>
                            </td>


                            <td class="text-end">

                                <button class="btn btn-outline-primary rounded py-1"
                                    popovertarget="popover-{{ $file->id }}">

                                    <i class="bi bi-folder me-2"></i>
                                    View

                                </button>




                                <!-- POPOVER -->
                    <div id="popover-{{ $file->id }}" popover
                        style="border: 1px solid #dee2e6;  max-width: 500px; width: 90%; overflow-y: auto; max-height: 90vh;">
                        <div class="container">
                            <div class="border-0">
                                <div class=" p-2 p-md-2">
                                    <h5 class="fw-bold text-start mb-2"> {{ $file->title }} </h5>

                                    <div class="text-muted text-start small mb-4">
                                         <span class="me-3">
                                             <i class="bi bi-eye"></i>
                                                {{ $file->views ?? 0 }}
                                        </span>

                                        <span class="me-3">
                                             <i class="bi bi-download"></i>
                                                {{ $file->downloads ?? 0 }}
                                        </span>
                                    </div>

                                    <div class="row g-4 mb-4">

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded-2 py-2 px-1 d-flex justify-content-center align-items-center"
                                                    style="width:45px; height:45px; background-color: #EFF6FF;">
                                                    <i class="fa-regular fa-user text-primary"></i>
                                                </div>

                                                <div class="text-start">
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

                                                <div class="text-start">
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

                                                <div class="text-start">
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

                                                <div class="text-start">
                                                    <strong>Keywords</strong><br>
                                                    {{ count(array_filter(explode(',', $file->auto_tag))) }} tags
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <h6 class="fw-bold text-start">Abstract</h6>
                                    <p class="text-muted" style="text-align: justify;">
                                        {{ $file->desc ?? 'No abstract available.' }}
                                    </p>

                                    <h6 class="fw-bold mt-4 text-start">Keywords</h6>
                                    <div class="text-start">
                                        @foreach(explode(',', $file->auto_tag) as $tag)
                                        <span class="small text-start rounded me-1 mb-1 px-2 py-1"
                                            style="background-color:#EFF6FF; color:#3B5FBF;">
                                            {{ trim($tag) }}
                                        </span>
                                    @endforeach
                                    </div>

                                    <div
                                        class="alert alert-primary d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                        <div>
                                            <div>
                                                <div class="fw-bold text-start">Full Research Document</div>
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
                                                class="btn btn-outline-primary mt-2">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>


                                        </div>
                                    </div>

                                    <!--ACTION-->
                                    <div class="d-flex gap-3">

                                       @if($file->status == 'pending')
                                            <div class="d-flex gap-3">

                                                <form action="{{ route('research.approve', $file->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success">
                                                        <i class="fa-regular fa-circle-check"></i>
                                                        Approve
                                                    </button>
                                                </form>

                                                <form action="{{ route('research.reject', $file->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fa-regular fa-circle-xmark"></i>
                                                        Reject
                                                    </button>
                                                </form>

                                            </div>
                                        @endif

                                    </div>


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

    </div>

</div>


    <!--ANALYTICS BUTTONS-->

    <div class="tab-pane fade" id="analytics">

            <div class="row g-3 mt-3">

            <h3>ANALYTICS Contents</h3>

            </div>
    </div>


    <!--TRACKING BUTTONS-->

    <div class="tab-pane fade" id="tracking">

            <div class="row g-3 mt-3">

            <h3>TRACKING  Contents</h3>

            </div>
    </div>


    <!--AUDIT BUTTONS-->

    <div class="tab-pane fade" id="audit">

            <div class="row g-3 mt-3">

            <h3>AUDIT  Contents</h3>

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
            @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <h5 class="fw-bold">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success') }}
            </h5>

            <hr>

            <p class="mb-2">
                <strong>User:</strong>
                {{ session('generated_name') }}
            </p>

            <p class="mb-1">
                <strong>Temporary Password:</strong>
            </p>

            <span class="badge bg-dark fs-6 px-3 py-2">
                {{ session('generated_password') }}
            </span>

            <p class="mt-3 mb-0 text-muted">
                Please provide this password to the user. They can change it after logging in.
            </p>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    @endif



    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">

        <i class="fa-solid fa-plus"></i>
        Add User

    </button>


    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">

                <!-- Header -->
                <div class="modal-header border-0 px-4 pt-4">
                    <h3 class="fw-bold">Add New User</h3>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
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

                            <select name="role" id="role" class="form-select form-select-lg rounded-3">

                                <option value="student">Student</option>
                                <option value="instructor">Instructor</option>
                                <option value="admin">Admin</option>

                            </select>
                        </div>
                        <!-- User ID -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                User ID <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="user_id" class="form-control form-control-lg rounded-3"
                                placeholder="e.g. 23-001" required>
                        </div>

                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" class="form-control form-control-lg rounded-3"
                                placeholder="e.g. Juan Dela Cruz" required>
                        </div>



                        <!-- Year Level -->
                        <div class="mb-3" id="yearLevelDiv">

                            <label class="form-label fw-semibold">
                                Year Level
                            </label>

                            <select name="year_level" id="yearLevel" class="form-select form-select-lg rounded-3">

                                <option value="">Select Year Level</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>

                            </select>

                        </div>

                        <!-- Password -->
                        <div class="alert alert-info d-flex align-items-center">

                            <i class="fa-solid fa-circle-info me-2"></i>

                            <div>
                                Password will be generated automatically after creating the account.
                            </div>

                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-3">

                            <button type="submit" class="btn btn-primary flex-fill py-3 rounded-3 fw-semibold">

                                <i class="fa-solid fa-user-plus me-2"></i>
                                Create User

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
        </div>
    </div>





    <!--users total-->
    <div class="container-fluid py-4">
        <div class="row g-4">

            <!-- Total Submissions -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-2">Total Users</p>
                            <h3 class="fw-normal mb-0">{{ count($users) }}</h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 rounded-2 p-3">
                            <i class="fa-regular fa-user text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary mb-2">Students</p>
                            <h3 class="fw-normal mb-0">{{ count($student) }}</h3>
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
                            <p class="text-secondary mb-2">Instructor</p>
                            <h3 class="fw-normal mb-0">{{ count($instructor) }}</h3>
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
                            <p class="text-secondary mb-2">Admin</p>
                            <h3 class="fw-normal mb-0">{{ count($admin) }}</h3>
                        </div>

                        <div class="bg-warning bg-opacity-10 rounded-2 p-3">
                            <i class="fa-regular fa-clock text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>



        </div>
    </div>

@endif









<!--RESEARCH-->
@if($view == 'research')

    <div class="container-fluid py-4">

    <div class="card rounded-5 shadow-sm border-0">

        <div class="card-body p-4">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

                <h3 class="fw-bold mb-3 mb-md-0">
                    All Research Submissions
                </h3>

                <div class="d-flex flex-wrap gap-3">

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

                </div>

            </div>

            <!-- Table -->
            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>Title</th>
                            <th>Author</th>
                            <th>Adviser</th>
                            <th>Submitted</th>
                            <th>Views</th>
                            <th>Downloads</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>

                        </tr>

                    </thead>

                    <tbody>
                        @foreach($studFiles as $file)

                        <tr>
                            <td style="max-width: 250px;">
                                <h6 class="fw-semibold mb-0 text-truncate">
                                    {{ $file->title }}
                                </h6>
                            </td>

                            <td>{{ $file->name }}</td>

                            <td class="text-muted">{{ $file->adviser }}</td>

                            <td> {{ $file->created_at->format('M d, Y') }}</td>

                            <td>

                                <span class="text-primary">

                                    <i class="bi bi-eye"></i>
                                    {{ $file->views ?? 0 }}
                                </span>

                            </td>

                            <td>

                                <span class="text-primary">

                                    <i class="bi bi-download"></i>
                                        {{ $file->downloads ?? 0 }}
                                </span>

                            </td>

                            <td>
                                <span class="rounded px-3 py-2">
                                   @if(empty($file->status) || $file->status == 'pending')
                                        <span class="badge bg-warning text-dark rounded-pill">
                                            Pending
                                        </span>
                                    @elseif($file->status == 'approved')
                                        <span class="badge bg-success rounded-pill">
                                            Approved
                                        </span>
                                    @elseif($file->status == 'rejected')
                                        <span class="badge bg-danger rounded-pill">
                                            Rejected
                                        </span>
                                    @endif

                                </span>
                            </td>


                            <td class="text-end">

                                <button class="btn btn-outline-primary rounded py-1"
                                    popovertarget="popover-{{ $file->id }}">

                                    <i class="bi bi-folder me-2"></i>
                                    View

                                </button>




                                <!-- POPOVER -->
                    <div id="popover-{{ $file->id }}" popover
                        style="border: 1px solid #dee2e6;  max-width: 500px; width: 90%; overflow-y: auto; max-height: 90vh;">
                        <div class="container">
                            <div class="border-0">
                                <div class=" p-2 p-md-2">
                                    <h5 class="fw-bold text-start mb-2"> {{ $file->title }} </h5>

                                    <div class="text-muted text-start small mb-4">
                                         <span class="me-3">
                                             <i class="bi bi-eye"></i>
                                                {{ $file->views ?? 0 }}
                                        </span>

                                        <span class="me-3">
                                             <i class="bi bi-download"></i>
                                                {{ $file->downloads ?? 0 }}
                                        </span>
                                    </div>

                                    <div class="row g-4 mb-4">

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 rounded-2 py-2 px-1 d-flex justify-content-center align-items-center"
                                                    style="width:45px; height:45px; background-color: #EFF6FF;">
                                                    <i class="fa-regular fa-user text-primary"></i>
                                                </div>

                                                <div class="text-start">
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

                                                <div class="text-start">
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

                                                <div class="text-start">
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

                                                <div class="text-start">
                                                    <strong>Keywords</strong><br>
                                                    {{ count(array_filter(explode(',', $file->auto_tag))) }} tags
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <h6 class="fw-bold text-start">Abstract</h6>
                                    <p class="text-muted" style="text-align: justify;">
                                        {{ $file->desc ?? 'No abstract available.' }}
                                    </p>

                                    <h6 class="fw-bold mt-4 text-start">Keywords</h6>
                                    <div class="text-start">
                                        @foreach(explode(',', $file->auto_tag) as $tag)
                                        <span class="small text-start rounded me-1 mb-1 px-2 py-1"
                                            style="background-color:#EFF6FF; color:#3B5FBF;">
                                            {{ trim($tag) }}
                                        </span>
                                    @endforeach
                                    </div>

                                    <div
                                        class="alert alert-primary d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                        <div>
                                            <div>
                                                <div class="fw-bold text-start">Full Research Document</div>
                                                <small>{{ basename($file->file_path) }}</small>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3 mt-md-0">

                                           <a href="{{ route('file.view', $file->id) }}" target="_blank"
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>
                                                View
                                            </a>

                                        </div>
                                    </div>

                                    <!--ACTION-->
                                    <div class="d-flex gap-3">

                                       @if($file->status == 'pending')
                                            <div class="d-flex gap-3">

                                                <form action="{{ route('research.approve', $file->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success">
                                                        <i class="fa-regular fa-circle-check"></i>
                                                        Approve
                                                    </button>
                                                </form>

                                                <form action="{{ route('research.reject', $file->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fa-regular fa-circle-xmark"></i>
                                                        Reject
                                                    </button>
                                                </form>

                                            </div>
                                        @endif

                                    </div>


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

    </div>

</div>

@endif







<!--REPOSITORY-->
@if($view == 'repository')

    Repository Content

@endif








<!--REPORTS-->
@if($view == 'reports')

    Reports Content

@endif













<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>


<!--bar graph-->
<script>
var chart = echarts.init(document.getElementById('monthlyChart'));

var option = {

    title: {
    text: 'Monthly Upload Trends',
    left: 'left',

    textStyle: {
        color: '#1F2937',
        fontSize: 13,
    }
    },


    tooltip:{
        trigger:'axis'
    },

    legend: {
    data: ['Uploads', 'Approvals'],
    bottom: 10,
    left: 'center'
},

    xAxis:{
        type:'category',
        data:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
    },

    yAxis:{
        type:'value'
    },

   series: [
    {
        name: 'Uploads',
        type: 'bar',
        itemStyle: {
            color: '#1E3A8A'
        },
        data: @json($uploadData)
    },
    {
        name: 'Approvals',
        type: 'bar',
        itemStyle: {
            color: '#10B981'
        },
        data: @json($approvalData)
    }
]

};

chart.setOption(option);
window.addEventListener('resize', function () {
    chart.resize();
    pieChart.resize();
});
</script>



<!--Pie chart-->
<script>
var pieChart = echarts.init(document.getElementById('pieChart'));

var option = {

 title: {
    text: 'Research Status Distribution',
    left: 'left',

    textStyle: {
        color: '#1F2937',
        fontSize: 13,
    }
    },
    tooltip: {
        trigger: 'item'
    },

    legend: {
        bottom: '10',
        left: 'center',
    },

    series: [
        {
            name: 'Research Status',
            type: 'pie',
            radius: ['40%', '70%'],

            avoidLabelOverlap: false,

            label: {
                show: false,
                position: 'center'
            },

            emphasis: {
                label: {
                    show: true,
                    fontSize: 24,
                    fontWeight: 'bold'
                }
            },

            labelLine: {
                show: false
            },

            data: [
                {
                    value: {{ $approvedCount }},
                    name: 'Approved',
                    itemStyle: {
                        color: '#198754'
                    }
                },
                {
                    value: {{ $pendingCount }},
                    name: 'Pending',
                    itemStyle: {
                        color: '#FFC107'
                    }
                },
                {
                    value: {{ $rejectedCount }},
                    name: 'Rejected',
                    itemStyle: {
                        color: '#DC3545'
                    }
                }
            ]
        }
    ]
};

pieChart.setOption(option);
window.addEventListener('resize', function () {
    chart.resize();
    pieChart.resize();
});
</script>

<!--Linechart-->
<script>
    var chart = echarts.init(document.getElementById('lineChart'));

var option = {

    title: {
        text: 'Downloads & Engagement Trends (Views vs Downloads)',
        left: 'left',
        textStyle: {
            color: '#1F2937',
            fontSize: 13,
        }
    },

    tooltip: {
        trigger: 'axis'
    },

    legend: {
        bottom: 10,
        left: 'center',
        data: ['Views', 'Downloads']
    },

    grid: {
        top: 70,
        left: '8%',
        right: '5%',
        bottom: 60,
        containLabel: true
    },

    xAxis: {
        type: 'category',
        boundaryGap: false,
        data:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
    },

    yAxis: {
        type: 'value'
    },

    series: [
    {
        name: 'Views',
        type: 'line',
        smooth: true,
        data: @json($viewData),

        lineStyle: {
            color: '#7C4DFF',
            width: 3
        },

        itemStyle: {
            color: '#FFFFFF',
            borderColor: '#7C4DFF',
            borderWidth: 3
        },

        symbol: 'circle',
        symbolSize: 8
    },

    {
        name: 'Downloads',
        type: 'line',
        smooth: true,
        data: @json($downloadData),

        lineStyle: {
            color: '#10B981',
            width: 3
        },

        itemStyle: {
            color: '#FFFFFF',
            borderColor: '#10B981',
            borderWidth: 3
        },

        symbol: 'circle',
        symbolSize: 8
    }
]
};

chart.setOption(option);

window.addEventListener('resize', function () {
    chart.resize();
});
</script>





@endsection
