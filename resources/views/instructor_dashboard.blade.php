@extends('layouts.instructor_layout')

@section('title', 'Instructor Dashboard')

@section('instructor_dashboard')


<br>


<!--search button-->
<div class="d-flex align-items-center gap-2 mb-3">
    <input type="search" name="" id="" placeholder="☞ Search by title, species, student, tag, or keywords..." class="form-control">
    <button type="submit" class="border border-info bg-light rounded"><i class="fa-solid fa-search"></i></button>
</div>

<!--buttons-->
<div class="d-flex gap-3 mb-3 flex-wrap">

    <!-- folders btn-->
        <a href="{{ route('instructor_dashboard', ['instructor_btn' => 'folders']) }}"
        class=" nav-link {{ request()->query('instructor_btn') === 'folders' ? 'active text-white' : 'text-dark' }} btn btn-outline-light shadow-sm text-dark d-flex align-items-center gap-2 px-1 rounded">
            <i class="fa-solid fa-folder {{ request()->query('instructor_btn') === 'folders' ? 'text-white' : '' }}"></i>
            Folders
        </a>

    <!-- files btn-->
        <a href="{{ route('instructor_dashboard', ['instructor_btn' => 'files']) }}"    
        class="nav-link {{ request()->query('instructor_btn') === 'files' ? 'active text-white' : 'text-dark' }} btn btn-outline-light shadow-sm text-dark d-flex align-items-center gap-2 px-1 rounded">
            <i class="fa-solid fa-file {{ request()->query('instructor_btn') === 'files' ? 'text-white' : '' }}"></i>
            All Files
        </a>


    <!-- upload btn-->
        <a href="{{ route('instructor_dashboard', ['instructor_btn' => 'Upload']) }}"
        style="background: #00C853;"
        class="{{ request()->query('instructor_btn') === 'Upload' ? 'active text-white' : 'text-dark' }} btn text-white d-flex align-items-center gap-2 px-1 rounded">
            <i class="fa-solid fa-file-arrow-up {{ request()->query('instructor_btn') === 'Upload' ? 'text-white' : '' }}"></i>
            Upload File
        </a>

    <!-- compare btn-->
        <a href="{{ route('instructor_dashboard', ['instructor_btn' => 'compare']) }}"
        class=" nav-link {{ request()->query('instructor_btn') === 'compare' ? 'active text-white' : 'text-dark' }} btn btn-outline-light shadow-sm text-dark d-flex align-items-center gap-2 px-1 rounded">
            <i class="fa-solid fa-code-compare {{ request()->query('instructor_btn') === 'compare' ? 'text-white' : '' }}"></i>
            Compare Files
        </a>
    

</div>




@if($instructor_btn === 'files')

<div class="border shadow bg-white rounded-3 p-3">
  <h4>My Documents</h4>
  <p class="text-muted">All laboratory documents uploaded by instructors.</p>

  @foreach ($files as $userfile)

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
          <p class="text-muted mb-0">• {{ $userfile->student }}</p>
          <p class="text-muted mb-0">• {{ $userfile->created_at->format('F d, Y') }}</p>
        </div>
      </div>

      <!-- RIGHT SIDE (BUTTONS) -->
      <div class="d-flex gap-2 ms-auto">
        <button class="btn btn-sm text-primary"><i class="fa-regular fa-eye"></i></button>
        <button class="btn btn-sm text-warning"><i class="fa-regular fa-edit"></i></button>
        <button class="btn btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
      </div>

    </div>

  </div>

  @endforeach
</div>


<!--folder view-->
@elseif($instructor_btn === 'folders')

 <div class="justify-content-end d-flex">
    <button id="expandAllBtn"
        class="btn text-white d-flex align-items-center gap-2 px-2 rounded-3 mb-3"
        style="background: #1E90FF;">
      <i class="fa-regular fa-folder-open"></i>
      Expand All
    </button>
 </div>


<div>

<!--buttpon for first year-->
        <div class="d-flex align-items-center gap-3 mb-3 mt-5">
    
            <button class="btn fw-bold text-white rounded-5 px-3 py-2"
            style="background: #2563eb;"
                    data-bs-toggle="collapse" data-bs-target="#firstyear">
                    <i class="fa-solid fa-book"></i>  
                    First Year 
                    <i class="fa-solid fa-angle-right text-light"></i>
            </button>

    <div class="d-flex align-items-center flex-grow-1">
        <div style="height:1px; background:#ccc;" class="flex-grow-1"></div>
        <span class="ms-2 text-muted">5 courses</span>
    </div>

</div>

<!--button for first sem-->
        <div class="collapse" id="firstyear">
            <button class="btn btn-outline-secondary fw-bold text-primary ms-3 mb-3 rounded-3 p-1" 
                style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);" 
                data-bs-toggle="collapse" data-bs-target="#firstsem">
                <i class="fa-solid fa-folder"></i>  
                First Semester
                <i class="fa-solid fa-angle-right text-muted"></i>
                <span class="text-muted fw-light">(2 Courses)</span>
            </button>

  <!-- First Semester Cards -->


 <!-- WRAPPER ng cards -->
<div class="row collapse" id="firstsem">

  <!-- CARD 1 sa first semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder text-primary me-2"></i>
        <strong>Fundamental Fisheries</strong>
        @php
            $fundamentalFiles = $files->where('folder', 'Fundamental Fisheries');
        @endphp

            <small class="text-muted d-block">{{ $fundamentalFiles->count() }} files</small>
<hr>
        @forelse($fundamentalFiles as $file)

                    <div class="d-flex gap-2">
                        <div><p>{{ $file->title }}</p></div>
                        <div class="ms-auto d-flex gap-3 py-2 pt-3">
                            <i class="fa-regular fa-eye fa-sm text-primary"></i>
                            <i class="fa-solid fa-trash fa-sm text-danger"></i>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No files uploaded yet.</p>

        @endforelse


        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white bg-primary">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 2 -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder text-primary me-2"></i>
        <strong>General Zoology</strong>
        
         @php
            $zoologyFiles = $files->where('folder', 'General Zoology');
        @endphp

            <small class="text-muted d-block">{{ $zoologyFiles->count() }} files</small>
<hr>
        @forelse($zoologyFiles as $file)

                 <div class="d-flex gap-2">
                        <div><p>{{ $file->title }}</p></div>
                        <div class="ms-auto d-flex gap-3 py-2 pt-3">
                            <i class="fa-regular fa-eye fa-sm text-primary"></i>
                            <i class="fa-solid fa-trash fa-sm text-danger"></i>
                        </div>
                    </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white bg-primary">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

</div>
</div>


<!--button for second sem first year -->
        
        <div class="collapse" id="firstyear">
            <button class="btn btn-outline-secondary fw-bold text-primary ms-3 mb-3 rounded-3 p-1" 
                style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px);" 
                data-bs-toggle="collapse" data-bs-target="#secondsem">
                <i class="fa-solid fa-folder"></i>  
                Second Semester
                <i class="fa-solid fa-angle-right text-muted"></i>
                <span class="text-muted fw-light">(3 Courses)</span>
            </button>

  <!-- Second Semester Cards -->


 <!-- WRAPPER ng cards -->
<div class="row collapse" id="secondsem">

  <!-- CARD 1 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder text-primary me-2"></i>
        <strong>Organic Analytic Chemistry</strong>
        
         @php
            $organicFiles = $files->where('folder', 'Organic Analytic Chemistry');
        @endphp

            <small class="text-muted d-block">{{ $organicFiles->count() }} files</small>
<hr>
        @forelse($organicFiles as $file)

                    <div class="d-flex gap-2">
                        <div><p>{{ $file->title }}</p></div>
                        <div class="ms-auto d-flex gap-3 py-2 pt-3">
                            <i class="fa-regular fa-eye fa-sm text-primary"></i>
                            <i class="fa-solid fa-trash fa-sm text-danger"></i>
                        </div>
                    </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white bg-primary">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 2 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder text-primary me-2"></i>
        <strong>Botany</strong>
       
         @php
            $botanyFiles = $files->where('folder', 'Botany');
        @endphp

            <small class="text-muted d-block">{{ $botanyFiles->count() }} files</small>
<hr>
        @forelse($botanyFiles as $file)

        <div class="d-flex gap-2">
                        <div><p>{{ $file->title }}</p></div>
                        <div class="ms-auto d-flex gap-3 py-2 pt-3">
                            <i class="fa-regular fa-eye fa-sm text-primary"></i>
                            <i class="fa-solid fa-trash fa-sm text-danger"></i>
                        </div>
                    </div>
                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white bg-primary">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

   <!-- CARD 3 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder text-primary me-2"></i>
        <strong>Aquaculture</strong>
        
         @php
            $aquacultureFiles = $files->where('folder', 'Aquaculture');
        @endphp

            <small class="text-muted d-block">{{ $aquacultureFiles->count() }} files</small>
<hr>
        @forelse($aquacultureFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white bg-primary">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

</div>
</div>





<!--buttpon for second year-->
        <div class="d-flex align-items-center gap-3 mb-3 mt-5">
    
            <button class="btn fw-bold text-white rounded-5 px-3 py-2"
            style="background: #0d9488;"
                    data-bs-toggle="collapse" data-bs-target="#secondyear">
                    <i class="fa-solid fa-book"></i>  
                    Second Year 
                    <i class="fa-solid fa-angle-right text-light"></i>
            </button>

    <div class="d-flex align-items-center flex-grow-1">
        <div style="height:1px; background:#ccc;" class="flex-grow-1"></div>
        <span class="ms-2 text-muted">7 courses</span>
    </div>

</div>

<!--button cards for first sem second year-->
        <div class="collapse" id="secondyear">
            <button class="btn btn-outline-secondary fw-bold ms-3 mb-3 rounded-3 p-1" 
                style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px); border:0.5px solid #0d9488; color: #0d9488;" 
                data-bs-toggle="collapse" data-bs-target="#firstsem">
                <i class="fa-solid fa-folder"></i>  
                First Semester
                <i class="fa-solid fa-angle-right text-muted"></i>
                <span class="text-muted fw-light">(2 Courses)</span>
            </button>



 <!-- WRAPPER ng cards -->
<div class="row collapse" id="firstsem">

  <!-- CARD 1 sa first semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Ichthyology</strong>
        
         @php
            $ichthyologyFiles = $files->where('folder', 'Ichthyology');
        @endphp

            <small class="text-muted d-block">{{ $ichthyologyFiles->count() }} files</small>
<hr>
        @forelse($ichthyologyFiles as $file)

            <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 2 -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Aquatic Ecology and Resources</strong>
         @php
            $aquaticEcologyFiles = $files->where('folder', 'Aquatic Ecology and Resources');
        @endphp

            <small class="text-muted d-block">{{ $aquaticEcologyFiles->count() }} files</small>
<hr>
        @forelse($aquaticEcologyFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

</div>
</div>


<!--button cards for second sem second year -->
        
        <div class="collapse" id="secondyear">
            <button class="btn btn-outline-secondary fw-bold ms-3 mb-3 rounded-3 p-1" 
                style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px); border:0.5px solid #0d9488; color: #0d9488;" 
                data-bs-toggle="collapse" data-bs-target="#secondsem">
                <i class="fa-solid fa-folder"></i>  
                Second Semester
                <i class="fa-solid fa-angle-right text-muted"></i>
                <span class="text-muted fw-light">(5 Courses)</span>
            </button>

  <!-- Second Semester Cards -->


 <!-- WRAPPER ng cards -->
<div class="row collapse" id="secondsem">

  <!-- CARD 1 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Oceanography</strong>
        
         @php
            $oceanographyFiles = $files->where('folder', 'Oceanography');
        @endphp

            <small class="text-muted d-block">{{ $oceanographyFiles->count() }} files</small>
<hr>
        @forelse($oceanographyFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 2 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Fisheries Meteorology</strong>
        
         @php
            $meteorologyFiles = $files->where('folder', 'Fisheries Meteorology');
        @endphp

            <small class="text-muted d-block">{{ $meteorologyFiles->count() }} files</small>
<hr>
        @forelse($meteorologyFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 3 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Aquaculture Technology</strong>
        
         @php
            $aquacultureFiles = $files->where('folder', 'Aquaculture Technology');
        @endphp

            <small class="text-muted d-block">{{ $aquacultureFiles->count() }} files</small>
<hr>
        @forelse($aquacultureFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 4 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Biochemistry</strong>
        
         @php
            $biochemistryFiles = $files->where('folder', 'Biochemistry');
        @endphp

            <small class="text-muted d-block">{{ $biochemistryFiles->count() }} files</small>
<hr>
        @forelse($biochemistryFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 5 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #0d9488;"></i>
        <strong>Microbiology</strong>
        
         @php
            $microbiologyFiles = $files->where('folder', 'Microbiology');
        @endphp

            <small class="text-muted d-block">{{ $microbiologyFiles->count() }} files</small>
<hr>
        @forelse($microbiologyFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #0d9488;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  

</div>
</div>




<!--buttpon for third year-->
        <div class="d-flex align-items-center gap-3 mb-3 mt-5">
    
            <button class="btn fw-bold text-white rounded-5 px-3 py-2"
            style="background: #4f46e5;"
                    data-bs-toggle="collapse" data-bs-target="#thirdyear">
                    <i class="fa-solid fa-book"></i>  
                    Third Year 
                    <i class="fa-solid fa-angle-right text-light"></i>
            </button>

    <div class="d-flex align-items-center flex-grow-1">
        <div style="height:1px; background:#ccc;" class="flex-grow-1"></div>
        <span class="ms-2 text-muted">7 courses</span>
    </div>

</div>

<!--button cards for first sem third year-->
        <div class="collapse" id="thirdyear">
            <button class="btn btn-outline-secondary fw-bold ms-3 mb-3 rounded-3 p-1" 
                style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px); border: 0.5px solid #4f46e5; color: #4f46e5;" 
                data-bs-toggle="collapse" data-bs-target="#firstsem">
                <i class="fa-solid fa-folder"></i>  
                First Semester
                <i class="fa-solid fa-angle-right text-muted"></i>
                <span class="text-muted fw-light">(4 Courses)</span>
            </button>

  <!-- First Semester Cards -->


 <!-- WRAPPER ng cards -->
<div class="row collapse" id="firstsem">

  <!-- CARD 1 sa first semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Aquaculture Engineering</strong>

         @php
            $aquacultureFiles = $files->where('folder', 'Aquaculture Engineering');
        @endphp

            <small class="text-muted d-block">{{ $aquacultureFiles->count() }} files</small>
<hr>
        @forelse($aquacultureFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 2 -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Fish Genetics</strong>
        
         @php
            $fishGeneticsFiles = $files->where('folder', 'Fish Genetics');
        @endphp

            <small class="text-muted d-block">{{ $fishGeneticsFiles->count() }} files</small>
<hr>
        @forelse($fishGeneticsFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        
        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>


  <!-- CARD 3 -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Breeding and Hatchery Managements</strong>

         @php
            $breedingFiles = $files->where('folder', 'Breeding and Hatchery Managements');
        @endphp

            <small class="text-muted d-block">{{ $breedingFiles->count() }} files</small>
<hr>
        @forelse($breedingFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 4 -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Post Harvest Fisheries</strong>
        
         @php
            $postHarvestFiles = $files->where('folder', 'Post Harvest Fisheries');
        @endphp

            <small class="text-muted d-block">{{ $postHarvestFiles->count() }} files</small>
<hr>
        @forelse($postHarvestFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

</div>
</div>


<!--button cards for second sem third year -->
        
        <div class="collapse" id="thirdyear">
            <button class="btn btn-outline-secondary fw-bold ms-3 mb-3 rounded-3 p-1" 
                style=" background-color: rgba(255, 254, 254, 0.39); backdrop-filter: blur(4px); border: 0.5px solid #4f46e5; color: #4f46e5;" 
                data-bs-toggle="collapse" data-bs-target="#secondsem">
                <i class="fa-solid fa-folder"></i>  
                Second Semester
                <i class="fa-solid fa-angle-right text-muted"></i>
                <span class="text-muted fw-light">(3 Courses)</span>
            </button>

  <!-- Second Semester Cards -->


 <!-- WRAPPER ng cards -->
<div class="row collapse" id="secondsem">

  <!-- CARD 1 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Physiology of Aquatic Organism</strong>
        
         @php
            $physiologyFiles = $files->where('folder', 'Physiology of Aquatic Organism');
        @endphp

            <small class="text-muted d-block">{{ $physiologyFiles->count() }} files</small>
<hr>
        @forelse($physiologyFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

  <!-- CARD 2 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Fish Products Development and Processing</strong>
        
         @php
            $fishProductsFiles = $files->where('folder', 'Fish Products Development and Processing');
        @endphp

            <small class="text-muted d-block">{{ $fishProductsFiles->count() }} files</small>
<hr>
        @forelse($fishProductsFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

   <!-- CARD 3 sa second semester -->
  <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
    <div class="card p-2 rounded-3 text-dark h-100 shadow-sm">
        <i class="fa-solid fa-folder me-2" style="color: #4f46e5;"></i>
        <strong>Capture Fisheries</strong>
        
         @php
            $captureFiles = $files->where('folder', 'Capture Fisheries');
        @endphp

            <small class="text-muted d-block">{{ $captureFiles->count() }} files</small>
<hr>
        @forelse($captureFiles as $file)

        <div class="d-flex gap-2">
                <div><p>{{ $file->title }}</p></div>
                    <div class="ms-auto d-flex gap-3 py-2 pt-3">
                        <i class="fa-regular fa-eye fa-sm text-primary"></i>
                        <i class="fa-solid fa-trash fa-sm text-danger"></i>
                </div>
        </div>

                @empty
                    <p class="text-muted pt-2">No files uploaded</p>
        @endforelse

        

        <div class="mt-2">
            <button class="w-100 rounded-3 border-0 text-white"
            style="background: #4f46e5;">
                <i class="fa-solid fa-upload"></i> 
                Upload File
            </button>
        </div>
    </div>
  </div>

</div>
</div>


</div>

@elseif($instructor_btn === 'Upload')

<div class="subject-folder">
    <div class="folder-header">
        

        <form action="{{ route('userfile.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-2 mt-3">
            @csrf
            <h5>Upload Lab Result</h5>
            <!-- Title -->
             <label for="">Title</label>
            <input type="text" name="title" class="form-control" placeholder="File Title" required>

            <!-- Type -->
             <label for="">Type</label>
            <div class="mb-3">
            <input list="typelist" name="type" class="form-control" placeholder="Type (e.g. Water Quality, Fish Growth, etc.)" required>
                <datalist id="typelist" name="type" >
                    <option value="Water Quality"></option>
                    <option value="Fish Growth"></option>
                    <option value="Breeding"></option>
                    <option value="Nutrition"></option>
                    <option value="Disease Analysis"></option>
                    <option value="Environmental"></option>
                </datalist>
            </div>

            <!--species-->
            <label for="">Species</label>
            <input type="text" name="species" class="form-control" placeholder="Species (e.g., Nile Tilapia, Milkfish, etc.)" required>

            <!--description-->
            <label for="">Description</label>
            <textarea name="description" class="form-control" placeholder="File Description" rows="3" required></textarea>

            <!-- Instructor -->
                <label for="">Instructor</label>
            <input type="text" name="instructor" class="form-control" placeholder="Instructor Name" required>

             <!-- Student -->
                <label for="">Student</label>
             <input type="text" name="student" class="form-control" placeholder="Student Name" required>

            <!-- File -->
            <label for="">File</label>
            <input type="file" name="file" class="form-control" required>

            <!-- Folder -->
                <label for="">Folder</label>
            <select name="folder" class="form-control" required>
                <option value="" disabled selected>Select Folder</option>
                @foreach(($allowedFolders ?? []) as $folderName)
                  <option value="{{ $folderName }}">{{ $folderName }}</option>
                @endforeach
            </select>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary">Submit</button>
                <button type="button" class="btn btn-outline-dark" id="hideUploadFormButton">Cancel</button>
            </div>
        </form>
    </div>
</div>


@elseif($instructor_btn === 'compare')
<h1>Compare Files</h1>


@endif

<br><br><br><br>
@endsection