@props([
    'studFiles',
    'years',
    'types',
    'sdgs',
    'search' => '',
    'year' => '',
    'type' => '',
    'sdg' => '',
    'sort' => 'recent',
    'listRoute',
    'detailRoute',
    'showDownload' => false,
    'isPublicRepository' => false,
])

@php
    $hasFilters = filled($search) || filled($year) || filled($type) || filled($sdg) || $sort !== 'recent';
    $advancedFilterCount = collect([$year, $type, $sdg])->filter(fn ($value) => filled($value))->count()
        + ($sort !== 'recent' ? 1 : 0);
@endphp

<main class="repository-browser">
    <header class="repository-heading">
        <div>
            <p class="repository-eyebrow mb-2">{{ $isPublicRepository ? 'Public collection' : 'Research library' }}</p>
            <h1>Fisheries Research Repository</h1>
            <p class="repository-intro mb-0">Explore studies and theses from the CSU Aparri Fisheries Program.</p>
        </div>

        <div class="repository-count" aria-live="polite">
            <i data-lucide="book-open" aria-hidden="true"></i>
            <span class="repository-count-number" data-results-total-display>{{ number_format($studFiles->total()) }}</span>
            <span class="repository-count-label" data-results-label-display>{{ \Illuminate\Support\Str::plural('paper', $studFiles->total()) }}</span>
        </div>
    </header>

    <form method="GET" action="{{ route($listRoute) }}" class="repository-filter-form"
        data-live-search data-turbo-frame="research-results">
        <div class="repository-search-control">
            <label for="repository-search" class="visually-hidden">Search the repository</label>
            <i data-lucide="search" aria-hidden="true"></i>
            <input id="repository-search" type="search" name="search" value="{{ $search }}"
                placeholder="Search title, researcher, tag, or keyword">
        </div>

        <button class="repository-filter-toggle d-lg-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#repository-filters" aria-expanded="{{ $advancedFilterCount > 0 ? 'true' : 'false' }}"
            aria-controls="repository-filters">
            <i data-lucide="sliders-horizontal" aria-hidden="true"></i>
            <span>Filters{{ $advancedFilterCount > 0 ? " ({$advancedFilterCount})" : '' }}</span>
        </button>

        <div id="repository-filters" class="repository-filter-panel collapse {{ $advancedFilterCount > 0 ? 'show' : '' }} d-lg-grid">
            <div class="repository-filter-control">
                <label for="repository-type">Type</label>
                <select id="repository-type" name="type" class="form-select">
                    <option value="">All types</option>
                    @foreach ($types as $option)
                        <option value="{{ $option }}" @selected((string) $type === (string) $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="repository-filter-control">
                <label for="repository-year">Year</label>
                <select id="repository-year" name="year" class="form-select">
                    <option value="">All years</option>
                    @foreach ($years as $option)
                        <option value="{{ $option }}" @selected((string) $year === (string) $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="repository-filter-control repository-filter-control-wide">
                <label for="repository-sdg">Sustainable Development Goal</label>
                <select id="repository-sdg" name="sdg" class="form-select">
                    <option value="">All SDGs</option>
                    @foreach ($sdgs as $option)
                        <option value="{{ $option->id }}" @selected((string) $sdg === (string) $option->id)>{{ $option->label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="repository-filter-control">
                <label for="repository-sort">Sort by</label>
                <select id="repository-sort" name="sort" class="form-select">
                    <option value="recent" @selected($sort === 'recent')>Newest first</option>
                    <option value="views" @selected($sort === 'views')>Most viewed</option>
                    <option value="downloads" @selected($sort === 'downloads')>Most downloaded</option>
                </select>
            </div>

            <div class="repository-filter-actions">
                <button type="submit" class="btn repository-search-button">
                    <i data-lucide="search" aria-hidden="true"></i>
                    <span>Search</span>
                </button>

                @if ($hasFilters)
                    <a href="{{ route($listRoute) }}" class="repository-clear-link">Clear</a>
                @endif
            </div>
        </div>
    </form>

    <turbo-frame id="research-results" data-turbo-action="advance">
        <section class="repository-results" aria-label="Research results" aria-live="polite">
            <span data-results-total hidden>{{ $studFiles->total() }}</span>
            <span data-results-label hidden>{{ \Illuminate\Support\Str::plural('paper', $studFiles->total()) }}</span>

            @if ($studFiles->isEmpty())
                <div class="repository-empty-state">
                    <i data-lucide="folder-open" aria-hidden="true"></i>
                    <h2>{{ $hasFilters ? 'No matching research' : 'No published research yet' }}</h2>
                    <p>{{ $hasFilters ? 'Try changing or clearing your search filters.' : 'Approved research will appear here once it is published.' }}</p>
                    @if ($hasFilters)
                        <a href="{{ route($listRoute) }}" class="btn repository-secondary-button">Clear filters</a>
                    @endif
                </div>
            @else
                <div class="repository-grid">
                    @foreach ($studFiles as $file)
                        <x-research.card
                            :file="$file"
                            :show-download="$showDownload"
                            :url="route($detailRoute, $file->id)"
                        />
                    @endforeach
                </div>

                @if ($studFiles->hasPages())
                    <nav class="repository-pagination" aria-label="Research result pages">
                        {{ $studFiles->onEachSide(1)->links() }}
                    </nav>
                @endif
            @endif
        </section>
    </turbo-frame>
</main>
