@props(['prefix' => 'research', 'required' => true])

@php
    $maxUploadMb = config('research.max_upload_mb', 10);
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold">
        Research Document
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <div class="research-dropzone" data-dropzone data-prefix="{{ $prefix }}" id="dropzone-{{ $prefix }}"
         data-max-bytes="{{ $maxUploadMb * 1024 * 1024 }}"
         data-analyze-url="{{ route('research.pdf.analyze') }}"
         data-required="{{ $required ? 'true' : 'false' }}"
         role="button" tabindex="0">

        <div data-dropzone-empty>
            <svg class="research-dropzone-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 16V4M12 4l-4 4M12 4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"
                      stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p class="mb-0">
                <button type="button" class="research-dropzone-link" data-dropzone-browse>Click to upload</button>
                <span class="text-muted">or drag and drop</span>
            </p>
        </div>

        <div data-dropzone-result class="d-none">
            <i class="fa-solid fa-file-pdf fa-lg text-danger me-2"></i>
            <span data-dropzone-filename class="fw-semibold"></span>
            <span data-dropzone-filesize class="text-muted small ms-2"></span>

            <div data-dropzone-status class="small mt-2"></div>



           <div data-dropzone-duplicate
            class="alert alert-warning d-none mt-3 mb-0">
            <div class="fw-semibold">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Duplicate Research Detected
            </div>

            <div data-dropzone-duplicate-message class="small mt-1"></div>
        </div>





            <div class="mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dropzone-replace>
                    Replace File
                </button>
            </div>
        </div>

        {{-- No `required` here: a hidden (d-none) required file input makes
             Chrome silently refuse to submit the form with no visible error,
             since it can't show the validation bubble on an unfocusable
             control. initDropzone() enforces the same rule with a visible
             message instead (see data-required above). --}}
        <input type="file" name="file" accept=".pdf,application/pdf" class="d-none"
               data-dropzone-input>
    </div>

    <div class="invalid-feedback d-block" data-dropzone-error></div>

    <small class="text-secondary">Accepted format: PDF, up to {{ $maxUploadMb }}&nbsp;MB.</small>
</div>
