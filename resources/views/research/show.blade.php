@extends($isPublicView ? 'layouts.public_layout' : 'layouts.repository_layout')

@section('title', $file->title)

@section($isPublicView ? 'public' : 'repository')
    @php
        $researchers = $file->researcherNames();
        $publishedYear = $file->publication_year ?? $file->created_at?->format('Y');
    @endphp

    <br><br>

    {{-- Notification alert --}}
   @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small d-flex align-items-center"
         role="alert"
         id="success-alert"
         style="position: fixed; top: 80px; right: 20px; z-index: 1080; max-width: 280px; margin: 0;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close btn-sm py-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        setTimeout(function () {
            const alert = document.getElementById('success-alert');

            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 3000);
    </script>
@endif

    <a href="{{ $backUrl }}"
    class="text-decoration-none text-secondary mb-3 d-inline-block">
    <i class="fa-solid fa-arrow-left me-2"></i>Back
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
            @if ($canAccessPdf && !$isPublicView)
                {{-- Repository view only --}}
                <a data-turbo="false"
                href="{{ route('file.view', $file->id) }}"
                class="btn research-primary-button">
                    <i data-lucide="eye" aria-hidden="true"></i>
                    <span>View PDF</span>
                </a>

                <a data-turbo="false"
                href="{{ route('file.download', $file->id) }}"
                class="btn repository-secondary-button">
                    <i data-lucide="download" aria-hidden="true"></i>
                    <span>Download</span>
                </a>

                <button type="button"
                        popovertarget="ai-chat-panel"
                        class="btn repository-secondary-button">
                    <i data-lucide="message-circle-question" aria-hidden="true"></i>
                    <span>Ask about this research</span>
                </button>

            @elseif ($isPublicView)
                {{-- Public view: always request access --}}
                <button type="button"
                        class="btn btn-sm text-white rounded-2 px-3"
                        style="background:#072a89;"
                        data-bs-toggle="modal"
                        data-bs-target="#permissionModal">
                    <i data-lucide="log-in" aria-hidden="true"></i>
                    Request Full Access
                </button>
            @endif
        </div>
    </header>

    <div class="research-detail-content">
        <article class="research-abstract">
            <p class="research-section-label">Abstract</p>
            <h2>About this research</h2>
            <p style="text-align:justify">
                {{ $file->desc ?: 'No abstract is available for this research.' }}
            </p>

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

    @if ($canAccessPdf)
        <x-chat.widget :stud-file-id="$file->id" :stud-file-title="$file->title" />
    @endif

    {{-- Permission modal --}}
    <x-research.permission :file="$file" />
@endsection
