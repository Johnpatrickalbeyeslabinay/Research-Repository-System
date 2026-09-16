@props([
    'file',
    // Every row is opt-out via $show, not opt-in per-caller markup, so a
    // new metadata row added here reaches every surface automatically.
    'show' => ['researchers', 'adviser', 'type', 'year', 'keywords', 'sdgs', 'stats'],
    'compact' => false,
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

    @if (in_array('type', $show, true) && $file->research_type)
        <div class="{{ $rowClass }}"><span class="text-muted">Type:</span> {{ $file->research_type }}</div>
    @endif

    @if (in_array('year', $show, true) && $publishedYear)
        <div class="{{ $rowClass }}"><span class="text-muted">Published:</span> {{ $publishedYear }}</div>
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
                <span class="me-3"><i class="bi bi-file-earmark-text me-1"></i>{{ $file->pdf_page_count }} pages</span>
            @endif
            <span class="me-3"><i class="bi bi-eye me-1"></i>{{ $file->views ?? 0 }}</span>
            <span><i class="bi bi-download me-1"></i>{{ $file->downloads ?? 0 }}</span>
        </div>
    @endif
</div>
