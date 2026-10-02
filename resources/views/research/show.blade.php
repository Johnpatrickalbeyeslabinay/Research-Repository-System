@extends($isPublicView ? 'layouts.public_layout' : 'layouts.repository_layout')

@section('title', $file->title)

@section($isPublicView ? 'public' : 'repository')
    @php
        $researchers = $file->researcherNames();
        $publishedYear = $file->publication_year ?? $file->created_at?->format('Y');
    @endphp

<br><br><br><br><br>

    <a href="{{ url()->previous(route('repository')) }}" class="text-decoration-none text-secondary mb-3 d-inline-block">
        <i class="fa-solid fa-arrow-left me-2"></i>Back
    </a>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold mb-3">{{ $file->title }}</h3>

            {{-- <x-research.meta :file="$file" /> --}}
            <x-research.meta
    :file="$file"
    :show-download-count="false"
/>

            <hr class="my-4">

            <h6 class="text-uppercase text-secondary small fw-bold mb-2">Abstract</h6>
            <p class="mb-4">{{ $file->desc ?? 'No abstract available.' }}</p>


             <!-- NOTIFICATION ALERT -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"
        role="alert"
        id="success-alert">

        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}

    </div>

    <script>
        setTimeout(function () {
            const alert = document.getElementById('success-alert');

            if (alert) {
                alert.classList.remove('show');

                setTimeout(function () {
                    alert.remove();
                }, 150);
            }
        }, 3000);
    </script>
@endif

            <div class="d-flex gap-2 flex-wrap">

            <!--kung file ay repository-->
                @if ($canAccessPdf)

                    <button type="button"
                            class="btn btn-sm text-white rounded-2 px-3"
                            style="background:#072a89;"
                            data-bs-toggle="modal"
                            data-bs-target="#permissionModal">
                        <i class="bi bi-eye me-1"></i>
                        Request Full Access
                    </button>

                    <button type="button"
                            class="btn btn-sm text-white rounded-2 px-3"
                            style="background:#072a89;"
                            data-bs-toggle="modal"
                            data-bs-target="#permissionModal">
                        <i class="bi bi-download me-1"></i>
                        Download
                    </button>

                    <button type="button" popovertarget="ai-chat-panel"
                        class="btn btn-outline-secondary rounded-2 px-4 py-2">
                        <i class="bi bi-chat-left-text me-2"></i>Ask about this research
                    </button>
                @else
                    {{-- file.view AND file.download are both auth-only, and
                         this page is deliberately open to anonymous
                         /public visitors — a View/Download button that
                         just silently redirected to /login (the old
                         behavior on the repository/public listing pages)
                         is worse than not showing one at all. --}}
                   <div class="d-flex gap-2 align-items-center">

                   <!--kung file ay nasa public viewing
                    <a data-turbo="false"
                        href="{{ route('public.research.pdf', $file->id) }}"
                        target="_blank"
                        class="btn btn-sm text-white rounded-2 px-3"
                        style="background:#072a89;">
                        <i class="bi bi-eye me-1"></i>
                        Request Full Access
                    </a>-->

                    <button type="button"
                            class="btn btn-sm text-white rounded-2 px-3"
                            style="background:#072a89;"
                            data-bs-toggle="modal"
                            data-bs-target="#permissionModal">
                        <i class="bi bi-eye me-1"></i>
                        Request Full Access
                    </button>

                    <a href="{{ route('public') }}"
                        class="btn btn-sm btn-outline-secondary rounded-2 px-3">
                        <i class="fa-solid fa-xmark me-1"></i>
                        Close
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



      {{-- Permission modal --}}
    <x-research.permission :file="$file" />
@endsection
