@props(['prefix' => 'research', 'seed' => [], 'otherStudents' => collect()])

{{-- Free-text researcher list: nobody is added automatically (not even the
     submitter — they're already recorded as the file's owner). Each row is
     either linked to a registered student (typed name matched one exactly)
     or a plain "guest" name with no account. See app/Models/ResearchAuthor
     and StudentController::syncAuthors(). --}}
<div class="mb-3" data-researcher-input data-prefix="{{ $prefix }}" id="researchers-{{ $prefix }}"
     data-seed='@json($seed)'>
    <label class="form-label fw-semibold">Researchers</label>

    <div class="researcher-list mb-2" data-researcher-list></div>

    <div class="researcher-empty text-muted small mb-2" data-researcher-empty>
        No researchers added yet.
    </div>

    <div class="input-group input-group-sm" style="max-width: 360px;">
        <input type="text" class="form-control" list="researcher-datalist-{{ $prefix }}"
               placeholder="e.g. John Smith" data-researcher-text-input>
        <button type="button" class="btn btn-outline-secondary" data-researcher-add-btn>
            <i class="bi bi-plus-lg"></i> Add
        </button>
    </div>

    <datalist id="researcher-datalist-{{ $prefix }}">
        @foreach ($otherStudents as $student)
            <option value="{{ $student->name }}" data-user-id="{{ $student->id }}"></option>
        @endforeach
    </datalist>

    <div data-researcher-hidden-inputs></div>

    <small class="text-muted d-block mt-1">
        Optional — add anyone who worked on this research. Names matching a registered student link to their account automatically.
    </small>
</div>
