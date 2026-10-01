<div class="modal fade"
     id="permissionModal"
     tabindex="-1"
     aria-labelledby="permissionModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border border-dark border-2 shadow">

            {{-- Header --}}
            <div class="modal-header border-0 px-4 pt-3 pb-2">

                <h5 class="modal-title fw-semibold" id="permissionModalLabel">
                    Request Full Paper Access
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <div class="modal-body px-4 pb-4">

                {{-- Research Information --}}
                <div class="bg-primary bg-opacity-10
                            border border-primary border-opacity-25
                            rounded-3 p-3 mb-3">

                    <div class="fw-semibold text-dark mb-2">
                        {{ $file->title }}
                    </div>

                    @php
                        $researchers = $file->authors
                            ->pluck('author_name')
                            ->filter()
                            ->values()
                            ->all();

                        if ($file->name && !in_array($file->name, $researchers, true)) {
                            array_unshift($researchers, $file->name);
                        }
                    @endphp

                    <div class="small text-secondary">
                        Researcher{{ count($researchers) > 1 ? 's' : '' }}:
                    </div>

                    @if (count($researchers))
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach ($researchers as $researcher)
                                <span class="badge rounded-pill text-dark border fw-normal px-2 py-1"
                                      style="background:#eef2ff;">
                                    {{ $researcher }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                </div>


                {{-- Request Form --}}
                <form action="{{ route('research.access-request.store', $file->id) }}" method="POST">
                @csrf

                    <div class="row g-3">

                        {{-- Full Name
                        <div class="col-md-6">

                            <label for="requesterName"
                                   class="form-label small fw-semibold mb-1">
                                Full Name
                            </label>

                            <input type="text"
                                   class="form-control rounded-3"
                                   id="requesterName"
                                   name="full_name"
                                   placeholder="Your full name"
                                   required>

                        </div>--}}

                        {{-- Email --}}
                        <div class="col-md-12">

                            <label for="requesterEmail"
                                   class="form-label small fw-semibold mb-1">
                                Email Address
                            </label>

                            <input type="email"
                                   class="form-control rounded-3"
                                   id="requesterEmail"
                                   name="email"
                                   placeholder="your.email@example.com"
                                   required>

                        </div>

                    </div>

                    {{-- Reason --}}
                    <div class="mt-3">

                        <label for="requestReason" class="form-label small fw-semibold mb-1">
                            Reason for Requesting Access
                        </label>

                        {{-- Selected reason appears here --}}
                        <div id="selectedReasonContainer" class="mb-2 d-none">
                            <div class="d-inline-flex align-items-center gap-2 rounded-pill bg-light p-0 px-1" style="border: 1px solid #072a89;">
                                <span class="small" id="selectedReasonText"></span>

                                <button type="button"
                                        class="btn btn-sm p-0 border-0 text-secondary"
                                        id="removeReason"
                                        aria-label="Remove selected reason">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Reason dropdown --}}
                        <select class="form-select rounded-3"
                                id="requestReason"
                                name="reason_access"
                                required>

                            <option value="" selected disabled>
                                Select a reason for requesting access...
                            </option>

                            <option value="Research Gap Identification">
                                Research Gap Identification
                            </option>

                            <option value="Related Study Comparison">
                                Related Study Comparison
                            </option>

                            <option value="Research Problem Identification">
                                Research Problem Identification
                            </option>

                            <option value="General Academic Learning">
                                General Academic Learning
                            </option>

                        </select>

                    </div>



                    {{-- Notice --}}
                    <div class="text-secondary small mt-2">
                        Your request will be reviewed by the author.
                    </div>

                    {{-- Buttons --}}
                    <div class="row g-2 mt-3">

                        <div class="col-6">

                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm w-100 rounded-3"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                        </div>

                        <div class="col-6">

                            <button type="submit"
                                    class="btn btn-sm w-100 rounded-3 text-white"
                                    style="background:#072a89;">

                                <i class="fa-regular fa-paper-plane me-1"></i>
                                Submit Request

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const reasonSelect = document.getElementById('requestReason');
    const selectedContainer = document.getElementById('selectedReasonContainer');
    const selectedText = document.getElementById('selectedReasonText');
    const removeReason = document.getElementById('removeReason');

    reasonSelect.addEventListener('change', function () {

        if (this.value) {
            selectedText.textContent = this.value;
            selectedContainer.classList.remove('d-none');
        }

    });

    removeReason.addEventListener('click', function () {

        reasonSelect.value = '';
        selectedText.textContent = '';
        selectedContainer.classList.add('d-none');

    });

});
</script>
