@extends('layouts.public_layout')

@section('title', $file->title)

@section('public')

    @php
        $researchers = $file->researcherNames();
        $publishedYear = $file->publication_year ?? $file->created_at?->format('Y');
    @endphp

    <br><br>

    <a href="{{ url('/') }}"
       class="text-decoration-none text-secondary mb-3 d-inline-block">
        <i class="fa-solid fa-arrow-left me-2"></i>Back
    </a>



{{-- Approved access notice --}}
<div class="rounded-4 mb-4 p-3 p-md-4"
     style="
        background: linear-gradient(135deg, #f0fdf4 0%, #f8fffb 100%);
        border: 1px solid #bbf7d0;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.08);
     ">

    <div class="d-flex align-items-center">

        {{-- Icon --}}
        <div class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle me-3"
             style="
                width: 46px;
                height: 46px;
                background: #dcfce7;
                color: #15803d;
             ">
            <i class="fa-solid fa-check" style="font-size: 18px;"></i>
        </div>

        {{-- Message --}}
        <div class="flex-grow-1">

            <div class="d-flex align-items-center gap-2 mb-1">

                <strong style="
                    color: #166534;
                    font-size: 15px;
                    font-weight: 700;
                ">
                    Access Approved
                </strong>

            </div>

            <p class="mb-0"
               style="
                    color: #4b5563;
                    font-size: 13.5px;
                    line-height: 1.5;
               ">
                Your request has been approved. You now have authorized
                access to view the full research document.
            </p>

        </div>

        {{-- Status icon --}}
        <div class="d-none d-md-block ms-3"
             style="color:#16a34a;">
            <i class="fa-solid fa-shield-check"
               style="font-size: 22px;"></i>
        </div>

    </div>

</div>

    <header class="research-detail-header">

        <div class="research-detail-heading">

            <div class="research-detail-kicker">

                @if ($file->research_type)
                    <span>{{ $file->research_type }}</span>
                @endif

                @if ($publishedYear)
                    <span>{{ $publishedYear }}</span>
                @endif

            </div>

            <h1>{{ $file->title }}</h1>

            @if (count($researchers))
                <p class="research-detail-authors">
                    {{ implode(', ', $researchers) }}
                </p>
            @endif

        </div>

        <div class="research-detail-actions">

            {{-- Approved requester can only view the PDF --}}
            <a data-turbo="false"
               target="_blank"
               href="{{ URL::signedRoute('access-requests.approved.pdf', [
                   'accessRequest' => $accessRequest->id,
               ]) }}"
               class="btn research-primary-button">

                <i data-lucide="eye" aria-hidden="true"></i>

                <span>View PDF</span>

            </a>

        </div>

    </header>

    <div class="research-detail-content">

        <article class="research-abstract">

            <p class="research-section-label">
                Abstract
            </p>

            <h2>
                About this research
            </h2>

            <p style="text-align:justify">
                {{ $file->desc ?: 'No abstract is available for this research.' }}
            </p>


            {{-- Keywords --}}
            @if ($file->relationLoaded('keywords') && $file->keywords->isNotEmpty())

                <section class="research-detail-taxonomy"
                         aria-labelledby="research-keywords-heading">

                    <h3 id="research-keywords-heading">
                        Keywords
                    </h3>

                    <div class="research-card-tags">

                        @foreach ($file->keywords as $keyword)

                            <span class="research-tag research-tag-keyword">
                                {{ $keyword->display_name }}
                            </span>

                        @endforeach

                    </div>

                </section>

            @endif


            {{-- SDGs --}}
            @if ($file->relationLoaded('sdgs') && $file->sdgs->isNotEmpty())

                <section class="research-detail-taxonomy"
                         aria-labelledby="research-sdgs-heading">

                    <h3 id="research-sdgs-heading">
                        Sustainable Development Goals
                    </h3>

                    <div class="research-card-tags">

                        @foreach ($file->sdgs as $goal)

                            <span class="research-tag research-tag-sdg">
                                {{ $goal->label }}
                            </span>

                        @endforeach

                    </div>

                </section>

            @endif

        </article>


        {{-- Research information --}}
        <aside class="research-detail-meta"
               aria-label="Research information">

            <p class="research-section-label">
                Research information
            </p>

            <dl>

                @if ($file->adviser)

                    <div>
                        <dt>Adviser</dt>
                        <dd>{{ $file->adviser }}</dd>
                    </div>

                @endif


                @if ($file->research_type)

                    <div>
                        <dt>Research type</dt>
                        <dd>{{ $file->research_type }}</dd>
                    </div>

                @endif


                @if ($publishedYear)

                    <div>
                        <dt>Published</dt>
                        <dd>{{ $publishedYear }}</dd>
                    </div>

                @endif


                @if ($file->pdf_page_count)

                    <div>
                        <dt>Document</dt>
                        <dd>{{ $file->pdf_page_count }} pages</dd>
                    </div>

                @endif

            </dl>


            <div class="research-detail-stats">

                <span>
                    <i data-lucide="eye" aria-hidden="true"></i>
                    {{ number_format($file->views ?? 0) }} views
                </span>

                <span>
                    <i data-lucide="download" aria-hidden="true"></i>
                    {{ number_format($file->downloads ?? 0) }} downloads
                </span>

            </div>


            <x-research.meta
                :file="$file"
                :show="['index-status']"
                compact
            />

        </aside>

    </div>

@endsection