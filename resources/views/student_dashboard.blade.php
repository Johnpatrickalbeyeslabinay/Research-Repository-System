@extends('layouts.student_layout')

@section('title', 'Student')

@section('student_dashboard')


  <br>
  <!--part ng subjects-->
  @if($view === 'subjects')

    <!--search button-->
    <div class="d-flex align-items-center gap-2 mb-3">
      <input type="search" name="" id="" placeholder="☞ Search by title..." class="form-control">
      <button type="submit" class="border border-info bg-light rounded"><i class="fa-solid fa-search"></i></button>
    </div>

    <div class="card shadow-sm border-0 p-3 mb-3" style="border-radius:15px; border:1px solid rgba(0, 187, 169, 0.3);">

      <div class="d-flex justify-content-between align-items-center flex-wrap">


        <div class="d-flex align-items-start gap-3">
          <div style="
            background: rgba(0, 187, 169, 0.1);
            padding:10px;
            border-radius:10px;">
            <i class="fa-solid fa-book text-success fs-5"></i>
          </div>

          <div>
            <h6 class="mb-1 fw-bold">1st Year Subjects</h6>
            <small class="text-muted">Your subjects organized by semester</small>
          </div>
        </div>

        <!-- BUTTON -->
        <button id="expandAllBtn" class="btn text-white d-flex align-items-center gap-2 px-3"
          style="background: linear-gradient(to right, #14b8a6, #06b6d4); border-radius:10px;">
          <i class="fa-regular fa-folder-open"></i>
          Expand All
        </button>

      </div>
    </div>


    <!-- subjects per sem -->
    <div>

      <!-- SEMESTER -->
      <div>
        <button class="btn btn-outline-secondary fw-bold text-primary mb-3 rounded-3 p-1"
          style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);" data-bs-toggle="collapse"
          data-bs-target="#firstSem">
          <i class="fa-solid fa-folder"></i>
          First Semester
          <i class="fa-solid fa-angle-right text-muted"></i>
        </button>


        <div class="collapse" id="firstSem">

          <!-- SUBJECT CARD -->
          <div class="card p-3 mb-3 rounded-3">
            <div class="d-flex flex-column">
              <div>
                <i class="fa-solid fa-folder text-primary me-2"></i>
                <strong>Fundamental Fisheries</strong>

                @php
                  $fundamentalFiles = $stud_file->where('folder', 'Fundamental Fisheries');
                @endphp

                <small class="text-muted d-block">{{ $fundamentalFiles->count() }} files</small>
                <hr>
                @forelse($fundamentalFiles as $file)

                  <div class="d-flex gap-2">
                    <div>
                      <p>{{ $file->title }}</p>
                    </div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                      <i class="fa-regular fa-eye fa-sm text-primary"></i>
                    </div>
                  </div>

                @empty
                  <p class="text-muted pt-2">No files uploaded</p>
                @endforelse



                <div class="mt-2">
                  <button class="w-100 rounded-3 p-0 border-0 text-white bg-primary">
                    <i class="fa-regular fa-eye"></i>
                    View
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="card p-3 mb-3 rounded-3">
            <div class="d-flex flex-column">
              <div>
                <i class="fa-solid fa-folder text-primary me-2"></i>
                <strong>General Zoology</strong>

                @php
                  $zoologyFiles = $stud_file->where('folder', 'General Zoology');
                @endphp

                <small class="text-muted d-block">{{ $zoologyFiles->count() }} files</small>
                <hr>
                @forelse($zoologyFiles as $file)

                  <div class="d-flex gap-2">
                    <div>
                      <p>{{ $file->title }}</p>
                    </div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                      <i class="fa-regular fa-eye fa-sm text-primary"></i>
                    </div>
                  </div>
                @empty
                  <p class="text-muted pt-2">No files uploaded</p>
                @endforelse


                <div class="mt-2">
                  <button class="w-100 rounded-3 p-0 border-0 text-white bg-primary">
                    <i class="fa-regular fa-eye"></i>
                    View
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>


      <!--2nd SEMESTER-->
      <div class="mt-3">
        <button class="btn btn-outline-secondary fw-bold text-primary mb-3 rounded-3 p-1"
          style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);" data-bs-toggle="collapse"
          data-bs-target="#secondSem">
          <i class="fa-solid fa-folder"></i>
          Second Semester
          <i class="fa-solid fa-angle-right text-muted"></i>
        </button>

        <div class="collapse" id="secondSem">

          <!-- SUBJECT CARD -->
          <div class="card p-3 mb-3 rounded-3">
            <div class="d-flex flex-column">
              <div>
                <i class="fa-solid fa-folder text-primary me-2"></i>
                <strong>Organic Analytic Chemistry</strong>

                @php
                  $organicFiles = $stud_file->where('folder', 'Organic Analytic Chemistry');
                @endphp

                <small class="text-muted d-block">{{ $organicFiles->count() }} files</small>
                <hr>
                @forelse($organicFiles as $file)

                  <div class="d-flex gap-2">
                    <div>
                      <p>{{ $file->title }}</p>
                    </div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                      <i class="fa-regular fa-eye fa-sm text-primary"></i>
                    </div>
                  </div>

                @empty
                  <p class="text-muted pt-2">No files uploaded</p>
                @endforelse

                <div class="mt-2">
                  <button class="w-100 rounded-3 p-0 border-0 text-white bg-primary">
                    <i class="fa-regular fa-eye"></i>
                    View
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="card p-3 mb-3 rounded-3">
            <div class="d-flex flex-column">
              <div>
                <i class="fa-solid fa-folder text-primary me-2"></i>
                <strong>Botany</strong>

                @php
                  $botanyFiles = $stud_file->where('folder', 'Botany');
                @endphp

                <small class="text-muted d-block">{{ $botanyFiles->count() }} files</small>
                <hr>
                @forelse($botanyFiles as $file)

                  <div class="d-flex gap-2">
                    <div>
                      <p>{{ $file->title }}</p>
                    </div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                      <i class="fa-regular fa-eye fa-sm text-primary"></i>
                    </div>
                  </div>

                @empty
                  <p class="text-muted pt-2">No files uploaded</p>
                @endforelse


                <div class="mt-2">
                  <button class="w-100 rounded-3 p-0 border-0 text-white bg-primary">
                    <i class="fa-regular fa-eye"></i>
                    View
                  </button>
                </div>
              </div>
            </div>
          </div>


          <div class="card p-3 rounded-3">
            <div class="d-flex flex-column">
              <div>
                <i class="fa-solid fa-folder text-primary me-2"></i>
                <strong>Aquaculture</strong>

                @php
                  $aquacultureFiles = $stud_file->where('folder', 'Aquaculture');
                @endphp

                <small class="text-muted d-block">{{ $aquacultureFiles->count() }} files</small>
                <hr>
                @forelse($aquacultureFiles as $file)

                  <div class="d-flex gap-2">
                    <div>
                      <p>{{ $file->title }}</p>
                    </div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                      <i class="fa-regular fa-eye fa-sm text-primary"></i>
                      <i class="fa-solid fa-trash fa-sm text-danger"></i>
                    </div>
                  </div>

                @empty
                  <p class="text-muted pt-2">No files uploaded</p>
                @endforelse


                <div class="mt-2">
                  <button class="w-100 rounded-3 p-0 border-0 text-white bg-primary">
                    <i class="fa-regular fa-eye"></i>
                    View
                  </button>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>



  @elseif($view === 'documents')

    <!--search button-->
    <div class="d-flex align-items-center gap-2 mb-3">
      <input type="search" name="" id="" placeholder="Search by title..." class="form-control">
      <button type="submit" class="border border-info bg-light rounded"><i class="fa-solid fa-search"></i></button>
    </div>

    <div class="border shadow bg-white rounded-3 p-3">
      <h4>My Documents</h4>
      <p class="text-muted">All laboratory documents uploaded by instructors.</p>

      @foreach ($stud_file as $userfile)

        <div class="border shadow-sm rounded-3 bg-light p-3 mb-3">

          <!-- TOP CONTENT -->
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

            <!-- LEFT SIDE -->
            <div class="flex-grow-1">
              <h6 class="d-flex align-items-center flex-wrap">
                <i class="fa-regular fa-file fa-xl me-2 text-primary"></i>
                <span class="text-break ms-4">{{ $userfile->title }}</span>
              </h6>

              <div class="d-flex flex-wrap gap-2 mt-2 ms-4">
                <p class="bg-primary text-white px-2 rounded-3 mb-0">{{ $userfile->type }}</p>
                <p class="text-muted mb-0">• {{ $userfile->instructor }}</p>
                <p class="text-muted mb-0">• {{ $userfile->created_at->format('F d, Y') }}</p>
              </div>
            </div>

            <!-- RIGHT SIDE (BUTTONS) -->
            <div class="d-flex gap-2 ms-auto">
              <button class="btn"><i class="fa-regular fa-eye text-primary"></i></button>
              <button class="btn"><i class="fa-solid fa-download text-warning"></i></button>
            </div>

          </div>

        </div>

      @endforeach
    </div>




    <!--Lab search-->
  @elseif($view === 'search')

    <div class="d-flex align-items-center ps-3 mb-4">

      <i class="fa-solid fa-search fa-lg text-primary p-4"></i>
      <div class="text-white m-0 flex-column ps-3">
        <h2 class="offcanvas-title fw-bolder text-dark"> Search Lab Results</h2>
        <span class="text-secondary"> Related to First Year subjects</span>
      </div>
    </div>

    <!--search button-->
    <div class="d-flex align-items-center gap-2 mb-3">
      <input type="search" name="" id="" placeholder="☞ Search by title, species, student, tag, or keywords..."
        class="form-control">
      <button type="submit" class="border border-info bg-light rounded"><i class="fa-solid fa-search"></i></button>
    </div>

    <div class="dropdown d-flex justify-content-center gap-2 ms-2 justify-content-md-start">

      <button class="btn ps-5 pe-5 text-dark" type="button" id="" data-bs-toggle="dropdown" aria-expanded="false"
        style="background: #fff; border: 0.5px solid rgb(207, 204, 204);">
        <i class="fa-solid fa-filter me-5 text-dark"></i> All Types <i class="fa-solid fa-angle-down ms-5"></i>
      </button>

      <ul class="dropdown-menu w-25 p-3" style="background: #fff; border: 0.5px solid rgba(13,202,240,0.35); ">
        <li><a class="dropdown-item {{ request('type') === null ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => null])) }}">All Types</a></li>

        <li><a class="dropdown-item {{ request('type') === 'Water Quality' ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => 'Water Quality'])) }}">Water
            Quality</a></li>

        <li><a class="dropdown-item {{ request('type') === 'Fish Growth' ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => 'Fish Growth'])) }}">Fish
            Growth</a></li>

        <li><a class="dropdown-item {{ request('type') === 'Breeding' ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => 'Breeding'])) }}">Breeding</a>
        </li>

        <li><a class="dropdown-item {{ request('type') === 'Nutrition' ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => 'Nutrition'])) }}">Nutrition</a>
        </li>

        <li><a class="dropdown-item {{ request('type') === 'Disease Analysis' ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => 'Disease Analysis'])) }}">Disease
            Analysis</a></li>

        <li><a class="dropdown-item {{ request('type') === 'Environmental' ? 'active' : '' }}"
            href="{{ route('student_dashboard', array_merge(request()->query(), ['type' => 'Environmental'])) }}">Environmental</a>
        </li>
      </ul>

      <button type="button" class="text-white fw-bolder rounded border-0"
        style="background: linear-gradient(to right, #14b8a6, #06b6d4);"><i class="fa-solid fa-code-compare "></i>
        Compare</button>

    </div>

    <div class="container-fluid px-2">

      <div class="row text-start g-3 m-0 justify-content-start">

        @foreach($stud_file as $userfile)

          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card p-3 rounded-3 text-dark h-100">

              <!-- DATE -->
              <p class="text-end fs-6 text-secondary">
                {{ \Carbon\Carbon::parse($userfile->created_at)->format('F d, Y') }}
              </p>

              <!-- TITLE -->
              <h6 class="fw-bold mt-2">{{ $userfile->title }}</h6>

              <!-- TYPE -->
              <span class="text-secondary">{{ $userfile->type }}</span>

              <!-- DESCRIPTION -->
              <p>
                {{ Str::limit($userfile->description, 55, '...') }}
              </p>

              <!-- TAG -->
              <div class="d-flex gap-2">
                <h6 class="cont">{{ $userfile->auto_tag }}</h6>
              </div>

              <hr style="border: 0.5px solid rgba(13,202,240,0.35);">

              <!-- INSTRUCTOR -->
              <p class="prof text-secondary">{{ $userfile->instructor }}</p>

              <!-- BUTTON -->
              <button popovertarget="my-popover-{{ $userfile->id }}" class="text-white fw-bolder rounded border-0 bg-primary">
                View Details
              </button>

            </div>
          </div>

          <!-- POPOVER  -->
          <div id="my-popover-{{ $userfile->id }}" popover style="background: #f7f6f6; border: 0.5px solid rgba(13,202,240,0.35); border-radius: 5px;
                   width: 90vw; max-width: 1000px; height: 85vh; overflow-y:auto; padding: 15px;">

            <h4 class="text-center pt-2 text-dark">{{ $userfile->title }}</h4>
            <p class="text-center text-secondary">Detailed laboratory result information</p>

            <h6 class="text-secondary">Type</h6>
            <p class="text-dark">{{ $userfile->type }}</p>

            <h6 class="text-secondary">Species</h6>
            <p class="text-dark">{{ $userfile->species }}</p>

            <h6 class="text-secondary">Description</h6>
            <p class="text-dark">{{ $userfile->description }}</p>

            <h6 class="text-secondary">Instructor</h6>
            <p class="text-dark">{{ $userfile->instructor }}</p>

            <h6 class="text-secondary">Auto Tag</h6>
            <h6 class="cont">{{ $userfile->auto_tag }}</h6>

            <h6>Submitted By</h6>
            <p class="text-dark">{{ $userfile->student }}</p>

            @if($userfile->file_path)
              <div class="p-2 rounded mt-3" style="background:#fff; border:0.5px solid rgba(13,202,240,0.35);">
                <h6 class="text-dark">
                  <i class="fa-regular fa-folder text-primary"></i> Laboratory Documents
                </h6>

                <div class="p-1 rounded" style="border:0.5px solid rgba(13,202,240,0.35);">
                  <h6 class="text-dark">
                    <i class="fa-regular fa-file text-primary"></i> {{ $userfile->file_path }}
                  </h6>
                </div>

                <button type="button" class="bg-primary text-white fw-bolder rounded border border-primary mt-2">
                  <i class="fa-regular fa-folder"></i> Quick Access to PDF
                </button>

                <button type="button" class="rounded text-primary p-1 mt-1"
                  style="background:#fff; border:0.5px solid rgb(13,110,253);">
                  <i class="fa-solid fa-download"></i> Download
                </button>
              </div>
            @endif

            <br><br>

            <!-- RELATED CONTENT -->
            <h4 class="text-center text-dark">Related Content</h4>

            <div class="row g-2">
              <div class="col-6 col-lg-3">
                <div class="p-2 border rounded text-dark">Sample Related</div>
              </div>
            </div>

          </div>

        @endforeach

      </div>
    </div>
  @endif



  <br><br><br><br><br>

@endsection