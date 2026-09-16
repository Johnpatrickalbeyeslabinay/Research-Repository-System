@props(['prefix' => 'research', 'sdgs', 'selected' => []])

@php
    // config/sdgs.php is the single source of truth for the official
    // name (also exposed as Sdg::$name / Sdg::$label for every other
    // surface — cards, the detail page, reviewer views).
    $sdgNames = config('sdgs');

    // Keyed by the sdgs.id used as the checkbox value (not .number) so the
    // JS can render "SDG {number} – {name}" badges without assuming id==number.
    $sdgById = $sdgs->mapWithKeys(fn ($s) => [
        $s->id => ['number' => $s->number, 'name' => $sdgNames[$s->number] ?? ''],
    ]);
@endphp

<div class="mb-3" id="sdgSection-{{ $prefix }}" data-sdg-by-id='@json($sdgById)'>
    <label class="form-label fw-semibold">
        Sustainable Development Goals (SDGs)
        <span class="text-danger">*</span>
    </label>

    {{-- AI-suggested SDGs land here, not pre-checked — each is added only
         when the student clicks it. See initSdgSection in
         research-submission.js. --}}
    <div data-sdg-suggestions class="sdg-suggestions d-none mb-2">
        <div class="small text-muted mb-1">
            <i class="bi bi-stars me-1"></i>Suggested — click to add
        </div>
        <div class="d-flex flex-column gap-1 mb-1" data-sdg-suggestion-rows></div>
        <div class="keyword-suggestions-actions">
            <button type="button" class="btn btn-link btn-sm p-0" data-sdg-suggestions-add-all>Add all</button>
            <span class="text-muted">·</span>
            <button type="button" class="btn btn-link btn-sm p-0 text-secondary" data-sdg-suggestions-dismiss>Dismiss</button>
        </div>
    </div>

    <div data-sdg-summary class="d-flex flex-wrap gap-2 mb-2 d-none"></div>

    <div class="dropdown">
        <button
            type="button"
            class="btn btn-light border w-100 text-start dropdown-toggle"
            id="sdgDropdown-{{ $prefix }}"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside">
            Select SDG(s)
        </button>

        <div class="dropdown-menu w-100 p-2 overflow-auto" style="max-height: 280px;">
            @foreach ($sdgs as $sdg)
                <div class="form-check py-2 px-3">
                    <input
                        class="form-check-input sdg-checkbox"
                        type="checkbox"
                        name="sdg[]"
                        value="{{ $sdg->id }}"
                        id="sdg-{{ $prefix }}-{{ $sdg->id }}"
                        {{ in_array($sdg->id, $selected) ? 'checked' : '' }}>

                    <label class="form-check-label" for="sdg-{{ $prefix }}-{{ $sdg->id }}">
                        <strong>SDG {{ $sdg->number }}</strong>
                        – {{ $sdgNames[$sdg->number] ?? '' }}
                    </label>
                </div>
            @endforeach

            <div class="border-top mt-2 pt-2">
                <button type="button" class="btn btn-primary w-100" data-sdg-done="{{ $prefix }}">
                    <i class="bi bi-check-lg me-1"></i>
                    Done
                </button>
            </div>
        </div>
    </div>

    <div class="invalid-feedback d-block d-none" data-sdg-error>Please select at least one SDG.</div>

    <small class="text-muted">
        Select one or more SDGs related to your research.
        AI suggestions are based on your research title and abstract — please review them before submission.
    </small>
</div>
