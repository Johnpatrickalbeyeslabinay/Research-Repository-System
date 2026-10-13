{{--
    AI abstract draft for a PDF with no detectable abstract. Hidden until
    /research/pdf/analyze reports can_generate_abstract; the draft is shown
    in the form's [data-abstract-preview] box for the student to accept or
    edit (see initAbstractGenerate in resources/js/research-submission.js).
--}}
<div class="d-none mt-2" data-abstract-generate>
    <p class="small text-muted mb-1">
        No abstract was found in your PDF. You can write one, or let AI draft one from your paper for you to review.
    </p>
    <button type="button" class="btn btn-sm btn-outline-primary" data-abstract-generate-btn
        data-generate-url="{{ route('research.abstract.generate') }}">
        <i class="bi bi-stars me-1"></i>
        Generate abstract with AI
    </button>
    <div class="small mt-1" data-abstract-generate-status></div>
</div>
