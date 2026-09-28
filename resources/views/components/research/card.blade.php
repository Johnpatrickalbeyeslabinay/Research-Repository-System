@props([
    'file',
    'showDownload' => true,
    'showDownloadCount' => true,
    'url' => null,
])

@php
    // Replaces what used to be a full hidden detail <div popover> injected
    // per card (N cards = N hidden detail blocks in the page) — now a
    // real, citable URL instead.
    $url = $url ?? route('research.show', $file->id);
@endphp

<div class="col-lg-4 col-md-6">
    <div class="card h-100 shadow-sm border-0 rounded-4">
        <div class="card-body d-flex flex-column">
            <h5 class="fw-bold mb-2">
                <a data-turbo-frame="_top" href="{{ $url }}" class="text-decoration-none text-dark">{{ $file->title }}</a>
            </h5>

            <x-research.meta :file="$file" compact :show="['researchers', 'type', 'year']" />

            <p class="card-text flex-grow-1 mt-2">
                {{ \Illuminate\Support\Str::limit($file->desc, 160) }}
            </p>

            @if ($file->relationLoaded('keywords') && $file->keywords->isNotEmpty())
                <div class="mb-3">
                    @foreach ($file->keywords->take(4) as $keyword)
                        <span class="small rounded me-1 mb-1 px-2 py-1 d-inline-block"
                            style="background-color:#EFF6FF; color:#3B5FBF;">
                            {{ $keyword->display_name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="text-secondary small mb-3">
                <i class="bi bi-eye"></i> {{ $file->views ?? 0 }}
                @if ($showDownloadCount)
                <span class="ms-3">
                    <i class="bi bi-download"></i> {{ $file->downloads ?? 0 }}
                </span>
                @endif
            </div>

            <div class="d-flex gap-2 mt-auto">
                <a data-turbo-frame="_top" href="{{ $url }}"
                    class="btn d-flex justify-content-center align-items-center flex-grow-1 rounded py-1 bg-white"
                    style="border: 1px solid #1d0677; color: navy;">
                    <i class="bi bi-folder me-2"></i>
                    View Details
                </a>

                {{-- Anonymous /public visitors never see this — file.download
                     is auth-only, and a button that just bounces to /login is
                     worse than not showing it; they get a proper prompt on
                     the detail page instead (research/show.blade.php). --}}
                @if ($showDownload)
                    <a data-turbo="false" href="{{ route('file.download', $file->id) }}"
                        class="btn d-flex justify-content-center align-items-center rounded py-1 px-3 bg-white"
                        style="border: 1px solid #dee2e6; color: #072a89;"
                        title="Download PDF">
                        <i class="bi bi-download"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
