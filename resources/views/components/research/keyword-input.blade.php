@props(['prefix' => 'research', 'seed' => []])

{{-- Extracted/AI-suggested keywords land in the tray below, not the real
     list — nothing from either source is added until the student clicks
     it. See initResearcherInput's sibling initKeywordInput in
     research-submission.js for the accept/dismiss logic. --}}
<div class="mb-3" data-keyword-input data-prefix="{{ $prefix }}" id="keywords-{{ $prefix }}"
     data-seed='@json($seed)'>
    <label class="form-label fw-semibold">Keywords</label>

    <div data-keyword-suggestions class="keyword-suggestions d-none mb-2">
        <div class="small text-muted mb-1" data-keyword-suggestions-label>
            <i class="bi bi-stars me-1"></i>Suggested — click to add
        </div>
        <div class="d-flex flex-wrap gap-2 mb-1" data-keyword-suggestion-chips></div>
        <div class="keyword-suggestions-actions">
            <button type="button" class="btn btn-link btn-sm p-0" data-keyword-suggestions-add-all>Add all</button>
            <span class="text-muted">·</span>
            <button type="button" class="btn btn-link btn-sm p-0 text-secondary" data-keyword-suggestions-dismiss>Dismiss</button>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-2" data-keyword-chips></div>

    <div class="input-group input-group-sm" style="max-width: 320px;">
        <input type="text" class="form-control" placeholder="Add a keyword" data-keyword-text-input>
        <button type="button" class="btn btn-outline-secondary" data-keyword-add-btn>
            <i class="bi bi-plus-lg"></i> Add
        </button>
    </div>

    <div data-keyword-hidden-inputs></div>

    <small class="text-muted d-block mt-1">
        Keywords found in your document or suggested by AI appear above for you to review — add or dismiss each one, then add your own as needed.
    </small>
</div>
