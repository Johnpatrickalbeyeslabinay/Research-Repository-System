@extends('layouts.public_layout')

@section('title', 'PUBLIC')

@section('public')



    <div class="container text-center mt-5">
        <h1>Fisheries Research Repository</h1>
        <p>Explore academic research from students and faculty of the Fisheries Program</p>
    </div>

    <!--search button-->
    <div class="d-flex align-items-center gap-2 mb-3">
        <input type="search" name="" id="" placeholder=" Search by title, author, tag, or keywords..." class="form-control">
        <button type="submit" class="border border-info bg-light rounded"><i class="fa-solid fa-search"></i></button>
    </div>



    <div class="container py-5">

        <!-- Filters -->
        <div class="filter-container">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Filter by Year</label>
                    <select class="form-select form-select-lg">
                        <option>All Years</option>
                        <option>2024</option>
                        <option>2023</option>
                        <option>2022</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Filter by Keyword</label>
                    <select class="form-select form-select-lg">
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
                                <span class="badge bg-info text-dark rounded-pill d-inline-block  w-md-auto text-wrap"
                                    style="white-space: normal;">
                                    {{ $file->auto_tag }}
                                </span>
                            </div>

                            <button class="btn btn-outline-primary w-100 mt-auto" popovertarget="popover-{{ $file->id }}">
                                <i class="bi bi-eye me-2"></i>
                                View Details
                            </button>

                        </div>

                    </div>

                </div>

                <!-- POPOVER -->
                <div id="popover-{{ $file->id }}" popover
                    style=" border: 1px solid #dee2e6; margin-top: 100px; max-width: 1200px; width: 90%;">

                    <div class="container py-5">

                        <div class="card shadow-sm border-0">

                            <div class="card-body p-4 p-md-5">

                                <h2 class="fw-bold mb-2">
                                    {{ $file->title }}
                                </h2>

                                <div class="text-muted small mb-4">
                                    <span class="me-3">
                                        <i class="bi bi-eye"></i>
                                        {{ $file->views ?? 0 }}
                                    </span>

                                    <span>
                                        <i class="bi bi-download"></i>
                                        {{ $file->downloads ?? 0 }}
                                    </span>
                                </div>

                                <div class="row g-3 mb-4">

                                    <div class="col-md-3 col-6">
                                        <strong>Author</strong><br>
                                        {{ $file->name }}
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <strong>Year Published</strong><br>
                                        {{ $file->created_at->format('Y') }}
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <strong>Submitted</strong><br>
                                        {{ $file->created_at->format('M j, Y') }}
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <strong>Keyword</strong><br>
                                        {{ $file->auto_tag }}
                                    </div>

                                </div>

                                <h5 class="fw-bold">Abstract</h5>

                                <p class="text-muted">
                                    {{ $file->desc ?? 'No abstract available.' }}
                                </p>

                                <h5 class="fw-bold mt-4">Keywords</h5>

                                <span class="badge bg-secondary">
                                    {{ $file->auto_tag }}
                                </span>

                                <div
                                    class="alert alert-primary d-flex justify-content-between align-items-center mt-5 flex-wrap">

                                    <div>
                                        <div class="fw-bold">
                                            Full Research Document
                                        </div>

                                        <small>
                                            Download the complete PDF document
                                        </small>
                                    </div>

                                    <button class="btn btn-primary mt-3 mt-md-0">
                                        <i class="bi bi-download me-2"></i>
                                        Download PDF
                                    </button>

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