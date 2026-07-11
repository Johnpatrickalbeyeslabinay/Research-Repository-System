@extends('layouts.home_layout')

@section('title', 'HOME')

@section('home')


    <br><br><br>


    <div class="text-center">
        <i class="fa-solid fa-book" style="font-size: 80px; color: #139184;"></i>
        <br><br>
        <h1 class="text-dark fw-bold">Student Research Portal</h1>
        <br>
        <h5 class="text-secondary">Submit, track, and share your fisheries research with the academic community. Contribute
            to the growing body of knowledge in marine and aquatic sciences.</h5>
    </div>


    <br><br><br><br>

    <div class="container">
        <div class="row text-center">

            <div class="col-12 col-lg-3 mb-3">
                <div class="p-4 border rounded shadow-sm h-100">
                    <i class="fa-solid fa-upload fs-1 mb-3"></i>
                    <h4 class="fw-bold">Submit Research</h4>
                    <p>Upload your research papers with comprehensive metadata and documentation</p>
                </div>
            </div>

            <div class="col-12 col-lg-3 mb-3">
                <div class="p-4 border rounded shadow-sm h-100">
                    <i class="fa-solid fa-chart-line fs-1 mb-3"></i>
                    <h4 class="fw-bold">Track Submissions</h4>
                    <p>Monitor the status of your research submissions in real-time</p>
                </div>
            </div>

            <div class="col-12 col-lg-3 mb-3">
                <div class="p-4 border rounded shadow-sm h-100">
                    <i class="fa-solid fa-search fs-1 mb-3"></i>
                    <h4 class="fw-bold">Browse Repository</h4>
                    <p>Search and explore approved research papers from fellow students</p>
                </div>
            </div>

            <div class="col-12 col-lg-3 mb-3">
                <div class="p-4 border rounded shadow-sm h-100">
                    <i class="fa-solid fa-award fs-1 mb-3"></i>
                    <h4 class="fw-bold">Academic Recognition</h4>
                    <p>Get your research published and recognized in the university repository</p>
                </div>
            </div>

        </div>
    </div>





    <div class="container py-5">

        <div class="bg-white shadow rounded-5 p-5">

            <h3 class="fw-bold text-center">
                How It Works
            </h3>

            <p class="text-secondary text-center mb-5">
                Simple steps to submit your research
            </p>


            <div class="row text-center g-4">

                <div class="col-12 col-md-4">

                    <div class=" rounded-circle bg-primary bg-opacity-25 
                            d-flex align-items-center justify-content-center
                            mx-auto" style=" width:80px;
                height:80px;">

                        <span class="fw-bold fs-1 text-primary">
                            1
                        </span>

                    </div>

                    <h3 class="fw-bold mt-4">
                        Login & Upload
                    </h3>

                    <p class="text-secondary">
                        Sign in with your student credentials and upload your research paper with all required metadata
                    </p>

                </div>


                <div class="col-12 col-md-4">

                    <div class="rounded-circle bg-success bg-opacity-25
                            d-flex align-items-center justify-content-center
                            mx-auto" style=" width:80px;
                height:80px;">

                        <span class="fw-bold fs-1 text-success">
                            2
                        </span>

                    </div>

                    <h3 class="fw-bold mt-4">
                        Faculty Review
                    </h3>

                    <p class="text-secondary">
                        Your instructor reviews your submission and provides feedback or approval
                    </p>

                </div>


                <div class="col-12 col-md-4">

                    <div class="step rounded-circle bg-success-subtle
                            d-flex align-items-center justify-content-center
                            mx-auto" style=" width:80px;
                height:80px;">

                        <span class="fw-bold fs-1 text-success">
                            <i class="fa-solid fa-check"></i>
                        </span>

                    </div>

                    <h3 class="fw-bold mt-4">
                        Get Published
                    </h3>

                    <p class="text-secondary">
                        Once approved, your research becomes part of the public repository
                    </p>

                </div>

            </div>

        </div>



        <div class="cta rounded-5 shadow text-center py-5 px-3 mt-5"
            style=" background:linear-gradient(to right,#1e3c97,#007c78);">

            <h1 class="text-white fw-bold">
                Ready to Share Your Research?
            </h1>

            <p class="text-white fs-4 mt-4">
                Join fellow students in contributing to fisheries research excellence
            </p>

            <button class="btn btn-light btn-lg px-5 mt-4 fw-semibold text-primary">
                <a href="{{ route('login') }}" class="text-decoration-none text-primary">
                    Login to Get Started
                </a>
            </button>

        </div>

    </div>




    <br><br><br><br><br><br><br>
@endsection