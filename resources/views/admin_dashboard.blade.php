@extends('layouts.admin_layout')

@section('title', 'Admin Dashboard')

@section('admin_dashboard')

<br><br>

@if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


<div class="container-fluid px-0 my-5">
  <div class="row g-3 justify-content-start">

    <!-- CARD -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl">
      <div class="card h-100 shadow-sm rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted">Total Instructors</h6>
          <h2 class="fw-bold">5</h2>
          </div>
          <div>
            <i class="fa-solid fa-users fa-2xl text-primary"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl">
      <div class="card h-100 shadow-sm rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted">Active Instructors</h6>
          <h2 class="fw-bold text-primary">2</h2>
          </div>
          <div>
            <i class="fa-solid fa-user-check fa-2xl text-success"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl">
      <div class="card h-100 shadow-sm rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted">Total Students</h6>
            <h2 class="fw-bold">10</h2>
          </div>
          <div>
            <i class="fa-solid fa-users fa-2xl text-primary"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl">
      <div class="card h-100 shadow-sm rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted">Active Students</h6>
            <h2 class="fw-bold text-primary">7</h2>
          </div>
          <div>
            <i class="fa-solid fa-user-check fa-2xl text-success"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-4 col-xl">
      <div class="card h-100 shadow-sm rounded-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted">Pending Accounts</h6>
            <h2 class="fw-bold text-danger">2</h2>
          </div>
          <div>
            <i class="fa-solid fa-clock fa-2xl text-danger"></i>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>


<div class="container-fluid">
  <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center mb-3">

    <!-- Heading -->
    <div>
      <h4>User Management</h4>
      <p>Manage Instructors and Students</p>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-2 mt-2 mt-md-0 ms-md-auto flex-wrap">
      <div class="d-flex gap-2">
        <input type="search" class="form-control" placeholder="Search by ID or Name...">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
      </div>
      <div>
        <button type="button" popovertarget="my-popover" class="btn btn-success"> <i class="fa-solid fa-plus"></i> Add Student</button>
      </div>



      <!--popover-->

      <div id="my-popover" popover 
      style="background: #f7f6f6; border: 0.5px solid rgba(13,202,240,0.35); border-radius: 5px;">
  <div class="container-fluid">
    
    <div class="card shadow border-0 mx-auto"
         style="max-width: 500px; width: 100%; border-radius:15px;">

      <div class="card-body p-4">

        <!-- HEADER -->
        <div class="mb-3">
          <h5 class="fw-bold mb-1">Add New Student</h5>
          <small class="text-muted">
            Create a new student account for Fisheries program
          </small>
        </div>
        

        <!-- FORM -->
        <form method="post" action="{{ route('register') }}">
          @csrf
          
          <!-- STUDENT ID -->
          <div class="mb-3">
            <label class="form-label fw-bold">Student ID</label>
            <input type="text" class="form-control"
                    name="stud_id"
                   placeholder="2024-0001"
                   value="{{ old('stud_id') }}">
          </div>

          <!-- NAME -->
          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold">First name</label>
              <input type="text" class="form-control"
                      name="first_name"
                     placeholder="Juan"
                     value="{{ old('first_name') }}">
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label fw-bold">Last name</label>
              <input type="text" class="form-control"
                      name="last_name"
                     placeholder="Dela Cruz"
                     value="{{ old('last_name') }}">
            </div>
          </div>

          <!--year_level-->
          <div class="mb-3">
            <label for="year_level" class="form-label fw-bold">Year Level</label>
            <select name="year_level" class="form-select ">
                <option value="1" {{ old('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                <option value="2" {{ old('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                <option value="3" {{ old('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
            </select>
          </div>

          <!-- PROGRAM -->
          <div class="mb-3">
            <label class="form-label fw-bold">Program</label>
            <input type="text" class="form-control"
                   name="program"
                   value="Fisheries" readonly>
          </div>

          <!-- PASSWORD -->
          <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" class="form-control"
                    name="password"
                   placeholder="Enter password"
                   value="{{ old('password') }}">
          </div>

          <div class="mb-3">
              <label class="form-label fw-bold">Confirm Password</label>
              <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm password">
          </div>

          <!-- ROLE (hidden default) -->
          <input type="hidden" name="role" value="{{ old('role', 'student') }}">


          <div class="bg-light border border-primary p-1 rounded-3 mb-3">
            <small class="text-primary"> <span class="badge text-primary">Note:</span> Instructors must register via the registration page and await approval. Only students can be created directly by admin.</small>
          </div>

          <!-- BUTTON -->
          <button type="submit"
                  class="btn w-100 text-white fw-semibold"
                  style="background: linear-gradient(to right, #14b8a6, #06b6d4); border-radius:10px;">
            <i class="fa-solid fa-user-plus me-2"></i>
            Add Student
          </button>

        </form>

      </div>
    </div>

  </div>
</div>


    </div>
  </div>

  



<div class="d-inline-flex gap-3 mb-3 flex-wrap rounded-5 bg-light px-1 py-1 shadow-sm mx-auto">

    <!-- instructor btn-->
        <a href="{{ route('admin_dashboard', ['admin_btn' => 'instructor']) }}"
        class=" nav-link {{ request()->query('admin_btn') === 'instructor' ? 'active text-white' : 'text-dark' }} btn btn-outline-light  px-2 py-1 text-dark d-flex align-items-center gap-2 px-1 rounded-5">
            <i class="fa-solid fa-folder {{ request()->query('admin_btn') === 'instructor' ? 'text-white' : '' }}"></i>
            Instructor ({{ $users->count() }})
        </a>

    <!-- student btn-->
        <a href="{{ route('admin_dashboard', ['admin_btn' => 'student']) }}"    
        class="nav-link {{ request()->query('admin_btn') === 'student' ? 'active text-white' : 'text-dark' }} btn btn-outline-light  px-2 py-1 text-dark d-flex align-items-center gap-2 px-1 rounded-5">
            <i class="fa-solid fa-file {{ request()->query('admin_btn') === 'student' ? 'text-white' : '' }}"></i>
            Student ({{ $users->count() }})
        </a>
</div>


<!-- instructor data -->
@if($admin_btn === 'instructor')

<div class="table-responsive  rounded-4">
    <table class="table align-middle">
        <thead class="bg-light">
            <tr>
                <th>Name</th>
                <th>Instructor ID</th>
                <th>Program</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>name</td>
                <td>9898</td>
                <td>090</td>
                <td>98989</td>

                <!-- STATUS -->
                <td>
                    <span class="badge bg-success px-3 py-2 rounded-pill">
                        Active
                    </span>
                </td>

                <!-- ACTIONS -->
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">

                        <!-- Edit -->
                        <button class="btn btn-outline-primary btn-sm rounded">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <!-- View -->
                        <button class="btn btn-outline-primary btn-sm rounded">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <!-- Deactivate -->
                        <button class="btn btn-outline-warning btn-sm rounded px-3">
                            Deactivate
                        </button>

                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!--student data-->
@elseif($admin_btn === 'student')

<div class="table-responsive rounded-4">
    <table class="table align-middle">
        <thead class="bg-light">
            <tr>
                <th>Name</th>
                <th>Student ID</th>
                <th>Program</th>
                <th>Year_Level</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                <td>{{ $user->stud_id }}</td>
                <td>{{ $user->program }}</td>
                <td>{{ $user->year_level }}</td>

                <!-- STATUS -->
                <td>
                    <span class="badge bg-success px-3 py-2 rounded-pill">
                        Active
                    </span>
                </td>

                <!-- ACTIONS -->
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">

                        <!-- Edit -->
                        <button class="btn btn-outline-primary btn-sm rounded">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <!-- View -->
                        <button class="btn btn-outline-primary btn-sm rounded">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <!-- Deactivate -->
                        <button class="btn btn-outline-warning btn-sm rounded px-3">
                            Deactivate
                        </button>

                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif

<br><br><br><br><br>

@endsection