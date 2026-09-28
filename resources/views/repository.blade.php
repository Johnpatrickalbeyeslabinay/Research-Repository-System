@extends('layouts.repository_layout')

@section('title', 'Repository')

@section('repository')

<br><br><br><br><br>
    <div class="container text-center mt-5">
        <h1>Fisheries Research Repository</h1>
        <p>Explore academic research from students and faculty of the Fisheries Program</p>
    </div>

    <!-- Research Count -->
    <div class="d-flex flex-column justify-content-center align-items-center text-center bg-light shadow-sm border border-1 rounded p-4 mx-auto mt-3 mb-3"
        style="width: 170px; height: 150px;">
        <i class="fa-solid fa-book fa-2xl text-primary mb-3"></i>
        <h1 class="fw-bold" data-results-total-display>{{ $studFiles->total() }}</h1>
        <small>Research Papers</small>
    </div>

    <!-- Search + filters -->
    {{-- data-live-search: resources/js/app.js submits this as you type
         (debounced) into the research-results frame below, so only the
         results are swapped in — the search box keeps focus and the URL
         stays shareable. --}}
    <form method="GET" action="{{ route('repository') }}" class="mb-3 mx-auto" style="max-width: 900px;"
        data-live-search data-turbo-frame="research-results">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="search" name="search" value="{{ $search }}"
                placeholder=" Search by title, author, tag, or keywords..."
                class="form-control bg-light" style="min-width: 220px; flex: 2 1 220px;">

            <!--<select name="type" class="form-select bg-light" style="max-width: 160px;">
                <option value="">All Types</option>
                @foreach ($types as $t)
                    <option value="{{ $t }}" {{ (string) $type === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>-->

            <select name="year" class="form-select bg-light" style="max-width: 140px;">
                <option value="">All Years</option>
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>

            <select name="sdg" class="form-select bg-light" style="max-width: 220px;">
                <option value="">All SDGs</option>
                @foreach ($sdgs as $s)
                    <option value="{{ $s->id }}" {{ (string) $sdg === (string) $s->id ? 'selected' : '' }}>{{ $s->label }}</option>
                @endforeach
            </select>

            <select name="sort" class="form-select bg-light" style="max-width: 160px;">
                <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>Newest first</option>
                <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>Most viewed</option>
                <option value="downloads" {{ $sort === 'downloads' ? 'selected' : '' }}>Most downloaded</option>
            </select>

            <button type="submit" class="border border-secondary bg-light rounded px-3">
                <i class="fa-solid fa-search"></i>
            </button>

            @if ($search || $year || $type || $sdg || $sort !== 'recent')
                <a href="{{ route('repository') }}" class="text-secondary small">Clear filters</a>
            @endif
        </div>
    </form>

    <turbo-frame id="research-results" data-turbo-action="advance">
    <div class="container py-5">
        <span data-results-total hidden>{{ $studFiles->total() }}</span>

        @if ($studFiles->isEmpty())
            <div class="text-center text-secondary py-5">
                <i class="fa-solid fa-folder-open fa-2xl mb-3 d-block"></i>
                @if ($search || $year || $type || $sdg)
                    No research matches your filters.
                @else
                    No research has been published yet.
                @endif
            </div>
        @else
            <!-- Research Cards -->
            <div class="row g-4 mt-3">
                @foreach ($studFiles as $file)
                    <x-research.card :file="$file" :show-download="$showDownload" />
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-5">
                {{ $studFiles->links() }}
            </div>
        @endif

    </div>
    </turbo-frame>

@endsection
