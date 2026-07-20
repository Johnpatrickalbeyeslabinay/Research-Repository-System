@extends('layouts.public_layout')

@section('title', 'PUBLIC')

@section('public')

    <a href="{{ route('home') }}" class="text-decoration-none text-secondary ms-1"><i
            class="fa-solid fa-arrow-left fa-xl"></i>
    </a>


    <div class="container text-center mt-5">
        <h1>Fisheries Research Repository</h1>
        <p>Explore academic research from students and faculty of the Fisheries Program</p>
    </div>

    <!-- Research Count -->
    <div class="d-flex flex-column justify-content-center align-items-center text-center bg-light shadow-sm border border-1 rounded p-4 mx-auto mt-3 mb-3"
        style="width: 170px; height: 150px;">
        <i class="fa-solid fa-book fa-2xl text-primary mb-3"></i>
        <h1 class="fw-bold">{{ count($studFiles) }}</h1>
        <small>Research Papers</small>
    </div>


    <!--search button-->
    <div class="d-flex align-items-center gap-2 mb-3 w-50 mx-auto">
        <input type="search" name="" id="" placeholder=" Search by title, author, tag, or keywords..."
            class="form-control bg-light">
        <button type="submit" class="border border-secondary bg-light rounded"><i class="fa-solid fa-search"></i></button>
    </div>



    <div class="container py-5">

        <!-- Filters -->
        <div class="filter-container">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Filter by Year</label>
                    <select class="form-select form-select-sm">
                        <option>All Years</option>
                        <option>2024</option>
                        <option>2023</option>
                        <option>2022</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Filter by Keyword</label>
                    <select class="form-select form-select-sm">
                        <option>All Keywords</option>
                        <option>Aquaculture</option>
                        <option>Conservation</option>
                        <option>Genetics</option>
                    </select>
                </div>
            </div>
        </div>



        <!-- Research Cards -->
        <div class="row g-4 mt-3">

            @foreach($studFiles as $file)

                <!-- CARD -->
                <div class="col-lg-4 col-md-6">

                    <div class="card h-100 shadow-sm border-0 rounded-4">

                        <div class="card-body d-flex flex-column">

                            <h5 class="card-title fw-bold">
                                {{ $file->title }}
                            </h5>

                            <p class="text-muted mb-2">
                                <strong>Author:</strong> {{ $file->name }}
                            </p>

                            <p class="text-muted mb-2">
                                <strong>Year:</strong> {{ $file->created_at->format('Y') }}
                            </p>

                            <div class="mb-3 text-secondary small">
                                <i class="bi bi-eye"></i>
                                {{ $file->views ?? 0 }}

                                <span class="ms-3">
                                    <i class="bi bi-download"></i>
                                    {{ $file->downloads ?? 0 }}
                                </span>
                            </div>

                            <p class="card-text flex-grow-1">
                                {{ \Illuminate\Support\Str::limit($file->desc, 100) }}
                            </p>

                            <div class="mb-3">
                                @foreach(explode(',', $file->auto_tag) as $tag)
                                    <span class="small rounded me-1 mb-1 px-2 py-1"
                                        style="background-color:#EFF6FF; color:#3B5FBF;">
                                        {{ trim($tag) }}
                                    </span>
                                @endforeach
                            </div>

                            <button
                                class="btn d-flex justify-content-center align-items-center w-100 rounded py-1 mt-3 bg-white"
                                style="border: 1px solid #1d0677; color: navy;" popovertarget="popover-{{ $file->id }}">

                                <i class="bi bi-folder me-2"></i>
                                View Details
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

                                    <!-- VIEW BUTTON -->
                                    <div class="d-flex gap-2 mt-3 mt-md-0">

                                        <a href="{{ route('file.view', ['id' => $file->id, 'v' => time()]) }}" target="_blank"
                                                class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>
                                                View
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



    <br><br><br><br><br><br><br>
@endsection
