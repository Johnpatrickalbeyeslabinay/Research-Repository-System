@extends('layouts.admin_layout')

@section('title', 'Admin Dashboard')

@section('admin')
    <br><br><br><br><br>

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

    <h1>walang nabago dito kasi wala lang</h1>

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


    <br><br><br><br><br>

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const role = document.getElementById('role');
            const yearDiv = document.getElementById('yearLevelDiv');
            const year = document.getElementById('yearLevel');

            function toggleYearLevel() {

                if (role.value === 'student') {

                    yearDiv.style.display = 'block';
                    year.required = true;

                } else {

                    yearDiv.style.display = 'none';
                    year.required = false;
                    year.value = '';

                }

            }

            toggleYearLevel();

            role.addEventListener('change', toggleYearLevel);

        });

    </script>

@endsection
