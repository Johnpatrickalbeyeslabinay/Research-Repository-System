@props(['file', 'url' => null, 'showDownload' => true])

@php
    $url = $url ?? route('research.show', $file->id);
    $researchers = $file->researcherNames();
    $publishedYear = $file->publication_year ?? $file->created_at?->format('Y');

    $tags = collect();
    if ($file->relationLoaded('keywords')) {
        foreach ($file->keywords as $keyword) {
            $tags->push(['label' => $keyword->display_name, 'kind' => 'keyword']);
        }
    }
    if ($file->relationLoaded('sdgs')) {
        foreach ($file->sdgs as $goal) {
            $tags->push(['label' => $goal->label, 'kind' => 'sdg']);
        }
    }
    $tags = $tags->unique('label')->values();
@endphp

<article class="research-card">
    <div class="research-card-body">
        <div class="research-card-heading">
            <div class="research-card-type-row">
                @if ($file->research_type)
                    <span class="research-type-badge">{{ $file->research_type }}</span>
                @endif
                @if ($publishedYear)
                    <span class="research-year">{{ $publishedYear }}</span>
                @endif
            </div>

            <h2 class="research-card-title">
                <a data-turbo-frame="_top" data-research-card-link href="{{ $url }}">{{ $file->title }}</a>
            </h2>

            <!--<p class="card-text flex-grow-1 mt-2 text-justify">
                {{ \Illuminate\Support\Str::limit($file->desc, 160) }}
            </p>-->

            <!--@if ($file->relationLoaded('keywords') && $file->keywords->isNotEmpty())
                <div class="mb-3">
                    @foreach ($file->keywords->take(4) as $keyword)
                        <span class="small rounded me-1 mb-1 px-2 py-1 d-inline-block"
                            style="background-color:#EFF6FF; color:#3B5FBF;">
                            {{ $keyword->display_name }}
                        </span>
                    @endforeach
                </div>
            @endif-->

        <p class="research-card-abstract">{{ $file->desc ?: 'No abstract is available for this research.' }}</p>

        @if ($tags->isNotEmpty())
            <div class="research-card-tags" aria-label="Keywords and sustainable development goals">
                @foreach ($tags->take(3) as $tag)
                    <span class="research-tag research-tag-{{ $tag['kind'] }}">{{ $tag['label'] }}</span>
                @endforeach
                @if ($tags->count() > 3)
                    <span class="research-tag research-tag-more">+{{ $tags->count() - 3 }}</span>
                @endif
            </div>
        @endif

        <div class="research-card-stats" aria-label="Research statistics">
            @if ($file->pdf_page_count)
                <span title="Page count"><i data-lucide="file-text" aria-hidden="true"></i>{{ $file->pdf_page_count }} pages</span>
            @endif
            <span title="Views"><i data-lucide="eye" aria-hidden="true"></i>{{ number_format($file->views ?? 0) }}</span>
            <span title="Downloads"><i data-lucide="download" aria-hidden="true"></i>{{ number_format($file->downloads ?? 0) }}</span>
        </div>
    </div>

    <div class="research-card-actions">
        <a data-turbo-frame="_top" data-research-card-link href="{{ $url }}" class="btn research-card-primary">
            View details
            <i data-lucide="arrow-right" aria-hidden="true"></i>
        </a>

        @if ($showDownload)
            <a data-turbo="false" href="{{ route('file.download', $file->id) }}"
                class="btn research-card-icon-button" aria-label="Download {{ $file->title }}" title="Download PDF">
                <i data-lucide="download" aria-hidden="true"></i>
            </a>
        @endif
    </div>
</article>
