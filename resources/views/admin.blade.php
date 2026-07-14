@extends('layouts.admin_layout')

@section('title', 'ADMIN')

@section('admin')


<!--DASHBOARD-->
@if($view == 'dashboard')

    Dashboard Content

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
                            <h3 class="fw-normal mb-0">1</h3>
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
                            <h3 class="fw-normal mb-0">1</h3>
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
                            <p class="text-secondary mb-2">Admin</p>
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

@endif

<!--RESEARCH-->
@if($view == 'research')

    Research Content

@endif

<!--REPOSITORY-->
@if($view == 'repository')

    Repository Content

@endif

<!--REPORTS-->
@if($view == 'reports')

    Reports Content

@endif


@endsection
