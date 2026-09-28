@extends('layouts.public_layout')

@section('title', $file->title)

@section('public')

    <a href="{{ url()->previous(route('repository')) }}" class="text-decoration-none text-secondary mb-3 d-inline-block">
        <i class="fa-solid fa-arrow-left me-2"></i>Back
    </a>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">

            <h3 class="fw-bold mb-3">{{ $file->title }}</h3>

            <x-research.meta :file="$file" />

            <hr class="my-4">

            <h6 class="text-uppercase text-secondary small fw-bold mb-2">Abstract</h6>
            <p class="mb-4">{{ $file->desc ?? 'No abstract available.' }}</p>

            <div class="d-flex gap-2 flex-wrap">
                @if ($canAccessPdf)
                    <a data-turbo="false" href="{{ route('file.view', ['id' => $file->id]) }}"
                        class="btn text-white rounded-2 px-4 py-2" style="background:#072a89;">
                        <i class="bi bi-eye me-2"></i>View PDF
                    </a>

                    <a data-turbo="false" href="{{ route('file.download', $file->id) }}"
                        class="btn btn-outline-secondary rounded-2 px-4 py-2">
                        <i class="bi bi-download me-2"></i>Download
                    </a>

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
                    <a href="{{ route('login') }}" class="btn text-white rounded-2 px-4 py-2" style="background:#072a89;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign in to view &amp; download
                    </a>
                @endif
            </div>

        </div>
    </div>

    @if ($canAccessPdf)
        {{-- Chat routes are auth-only (routes/web.php) — matches
             $canAccessPdf (Auth::check()) exactly, so this never renders
             for the anonymous /public visitors this page is otherwise
             open to. --}}
        <x-chat.widget :stud-file-id="$file->id" :stud-file-title="$file->title" />
    @endif

@endsection
