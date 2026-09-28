@extends($isPublicView ? 'layouts.public_layout' : 'layouts.repository_layout')

@section('title', $file->title)

@section($isPublicView ? 'public' : 'repository')
    @php
        $researchers = $file->researcherNames();
        $publishedYear = $file->publication_year ?? $file->created_at?->format('Y');
    @endphp

    <main class="research-detail">
        <a href="{{ $backUrl }}" class="research-back-link">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            <span>Back to repository</span>
        </a>

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
                    <p class="research-detail-authors">{{ implode(', ', $researchers) }}</p>
                @endif
            </div>

            <div class="research-detail-actions">
                @if ($canAccessPdf)
                    <a data-turbo="false" href="{{ route('file.view', $file->id) }}" class="btn research-primary-button">
                        <i data-lucide="eye" aria-hidden="true"></i>
                        <span>View PDF</span>
                    </a>

                    <a data-turbo="false" href="{{ route('file.download', $file->id) }}" class="btn repository-secondary-button">
                        <i data-lucide="download" aria-hidden="true"></i>
                        <span>Download</span>
                    </a>

                    <button type="button" popovertarget="ai-chat-panel" class="btn repository-secondary-button">
                        <i data-lucide="message-circle-question" aria-hidden="true"></i>
                        <span>Ask about this research</span>
                    </button>
                @elseif ($isPublicView && auth()->check())
                    <a href="{{ route('research.show', $file->id) }}" class="btn research-primary-button">
                        <span>Open in repository</span>
                        <i data-lucide="arrow-right" aria-hidden="true"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn research-primary-button">
                        <i data-lucide="log-in" aria-hidden="true"></i>
                        <span>Sign in to view PDF</span>
                    </a>
                @endif
            </div>
        </header>

        <div class="research-detail-content">
            <article class="research-abstract">
                <p class="research-section-label">Abstract</p>
                <h2>About this research</h2>
                <p>{{ $file->desc ?: 'No abstract is available for this research.' }}</p>

                @if ($file->relationLoaded('keywords') && $file->keywords->isNotEmpty())
                    <section class="research-detail-taxonomy" aria-labelledby="research-keywords-heading">
                        <h3 id="research-keywords-heading">Keywords</h3>
                        <div class="research-card-tags">
                            @foreach ($file->keywords as $keyword)
                                <span class="research-tag research-tag-keyword">{{ $keyword->display_name }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($file->relationLoaded('sdgs') && $file->sdgs->isNotEmpty())
                    <section class="research-detail-taxonomy" aria-labelledby="research-sdgs-heading">
                        <h3 id="research-sdgs-heading">Sustainable Development Goals</h3>
                        <div class="research-card-tags">
                            @foreach ($file->sdgs as $goal)
                                <span class="research-tag research-tag-sdg">{{ $goal->label }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>

            <aside class="research-detail-meta" aria-label="Research information">
                <p class="research-section-label">Research information</p>
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
                    <span><i data-lucide="eye" aria-hidden="true"></i>{{ number_format($file->views ?? 0) }} views</span>
                    <span><i data-lucide="download" aria-hidden="true"></i>{{ number_format($file->downloads ?? 0) }} downloads</span>
                </div>

                <x-research.meta :file="$file" :show="['index-status']" compact />
            </aside>
        </div>
    </main>

    @if ($canAccessPdf)
        <x-chat.widget :stud-file-id="$file->id" :stud-file-title="$file->title" />
    @endif
@endsection
