@props([
    'file',
    // Every row is opt-out via $show, not opt-in per-caller markup, so a
    // new metadata row added here reaches every surface automatically.
    'show' => ['researchers', 'adviser', 'type', 'year', 'keywords', 'sdgs', 'stats', 'index-status'],
    'compact' => false,
     'showDownloadCount' => true,
])

@php
    // researcherNames() is the whole point of this component: every view
    // before it rendered $file->name alone (the submitter snapshot),
    // silently hiding every co-author and guest researcher added through
    // the researcher picker. Requires 'authors' to be eager-loaded by the
    // caller or this N+1s.
    $researchers = $file->researcherNames();

    $keywordNames = $file->relationLoaded('keywords')
        ? $file->keywords->pluck('display_name')
        : collect();

    $sdgList = $file->relationLoaded('sdgs') ? $file->sdgs : collect();

    // Falls back to the submission date only for the handful of legacy
    // records that predate the publication_year field — every surface
    // used to show created_at unconditionally, mislabeled "Year
    // Published", so a 2019 thesis uploaded today read as "2026".
    $publishedYear = $file->publication_year ?? $file->created_at?->format('Y');

    $rowClass = $compact ? 'mb-1 small' : 'mb-2';

    // Whether the AI assistant can actually answer questions about this
    // paper. Only 'ready' documents are retrievable (see
    // VectorSearchService's `sf.search_index_status = 'ready'`), so a paper
    // stuck at not_started/processing/failed is silently invisible to every
    // answer with nothing on screen to explain why. Shown only to people
    // who could act on it — the submitter, their adviser, and admins —
    // since it is an operational detail, not part of the research record.
    $indexStatus = $file->search_index_status ?? 'not_started';
    $viewer = auth()->user();
    $canSeeIndexStatus = $viewer !== null && (
        $viewer->role === 'admin'
        || $viewer->id === $file->user_id
        || $viewer->id === $file->adviser_id
    );

    $indexNotice = match ($indexStatus) {
        'ready' => null,
        'failed' => ['bi-exclamation-triangle', 'text-danger', 'Not searchable — AI indexing failed. Re-submitting the PDF will retry it.'],
        'processing' => ['bi-arrow-repeat', 'text-secondary', 'Indexing for AI search…'],
        default => ['bi-clock', 'text-secondary', 'Queued for AI search indexing.'],
    };
@endphp

<div {{ $attributes }}>
    @if (in_array('researchers', $show, true) && count($researchers))
        <div class="{{ $rowClass }}">
            <span class="text-muted">Researcher{{ count($researchers) > 1 ? 's' : '' }}:</span>
            @if ($compact)
                {{ implode(', ', $researchers) }}
            @else
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach ($researchers as $researcher)
                        <span class="badge rounded-pill text-dark border px-3 py-1" style="background:#eef2ff;">
                            {{ $researcher }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if (in_array('adviser', $show, true) && $file->adviser)
        <div class="{{ $rowClass }}"><span class="text-muted">Adviser:</span> {{ $file->adviser }}</div>
    @endif

    <!--@if (in_array('type', $show, true) && $file->research_type)
        <div class="{{ $rowClass }}"><span class="text-muted">Type:</span> {{ $file->research_type }}</div>
    @endif-->

    @if (in_array('year', $show, true) && $publishedYear)
        <div class="{{ $rowClass }}"><span class="text-muted">Published:</span> {{ $publishedYear }}</div>
    @endif

   @if (in_array('year', $show, true) && $file?->created_at)
    <div class="{{ $rowClass }}">
        <span class="text-muted">Submitted:</span>
        {{ $file->created_at->format('F j, Y') }}
    </div>
    @endif

    @if (in_array('keywords', $show, true) && $keywordNames->isNotEmpty())
        <div class="{{ $rowClass }}">
            <span class="text-muted">Keywords:</span>
            <div class="d-flex flex-wrap gap-2 mt-1">
                @foreach ($keywordNames as $keyword)
                    <span class="badge rounded-pill text-dark border px-3 py-1" style="background:#eef2ff;">
                        {{ $keyword }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    @if (in_array('sdgs', $show, true) && $sdgList->isNotEmpty())
        <div class="{{ $rowClass }}">
            <span class="text-muted">SDGs:</span>
            <div class="d-flex flex-wrap gap-2 mt-1">
                @foreach ($sdgList as $sdg)
                    <span class="badge rounded-pill text-dark border px-3 py-1" style="background:#f3e8ff;">
                        {{ $sdg->label }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

   @if (in_array('stats', $show, true))
    <div class="{{ $rowClass }} text-secondary">

        @if ($file->pdf_page_count)
            <span class="me-3">
                <i class="bi bi-file-earmark-text me-1"></i>
                {{ $file->pdf_page_count }} pages
            </span>
        @endif

        <span class="me-3">
            <i class="bi bi-eye me-1"></i>
            {{ $file->views ?? 0 }}
        </span>

        @if ($showDownloadCount)
            <span>
                <i class="bi bi-download me-1"></i>
                {{ $file->downloads ?? 0 }}
            </span>
        @endif

    </div>
@endif

    @if (in_array('index-status', $show, true) && $indexNotice && $canSeeIndexStatus)
        <div class="{{ $rowClass }} small {{ $indexNotice[1] }}">
            <i class="bi {{ $indexNotice[0] }} me-1"></i>{{ $indexNotice[2] }}
        </div>
    @endif
</div>
