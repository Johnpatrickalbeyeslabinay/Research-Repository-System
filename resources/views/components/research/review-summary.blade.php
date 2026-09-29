@props(['prefix' => 'research', 'expanded' => false])

<div class="mb-3" data-review-summary data-prefix="{{ $prefix }}">
    @unless ($expanded)
        <button class="btn btn-outline-secondary btn-sm w-100" type="button"
                data-bs-toggle="collapse" data-bs-target="#reviewCollapse-{{ $prefix }}">
            <i class="bi bi-eye me-1"></i> Review before submitting
        </button>
    @endunless

    <div @if($expanded) class="mt-2" @else class="collapse mt-2" id="reviewCollapse-{{ $prefix }}" @endif>
        <div class="card border-0 bg-light rounded-4">
            <div class="card-body small">
                <div class="row gy-2">
                    <div class="col-12">
                        <span class="text-muted">Title:</span>
                        <div class="fw-semibold" data-review="title">—</div>
                    </div>
                    <!--<div class="col-6">
                        <span class="text-muted">Type:</span>
                        <div data-review="research_type">—</div>
                    </div>-->
                    <div class="col-6">
                        <span class="text-muted">Publication Year:</span>
                        <div data-review="publication_year">—</div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Adviser:</span>
                        <div data-review="adviser">—</div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Researchers:</span>
                        <div data-review="researchers">—</div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Abstract:</span>
                        <div class="text-truncate-3" data-review="desc">—</div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">Keywords:</span>
                        <div data-review="keywords">—</div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted">SDGs:</span>
                        <div data-review="sdgs">—</div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Document:</span>
                        <div data-review="file">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
