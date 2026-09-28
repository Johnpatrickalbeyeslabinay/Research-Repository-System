<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level 'auth' + 'role:student' middleware already gate this
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'research_type' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),

            // 'adviser_id' => 'required|exists:users,id',

            'advisers' => [
                'required',
                'array',
            ],

            'advisers.*' => [
                'required',
                'integer',
                'distinct',
                'exists:users,id',
            ],
            // Ordered researcher rows from the researcher-input component —
            // replaces the old co_authors[] (bare user ids only, picked
            // from a fixed list). A row may or may not be linked to a
            // real account: 'name' is always required (it's what's shown
            // and stored either way), 'user_id' is only present when the
            // typed name matched a registered student.
            'researchers' => 'nullable|array|max:20',
            'researchers.*.name' => 'required|string|max:255',
            'researchers.*.user_id' => 'nullable|exists:users,id',
            // Capped at 5000 chars: this is also sent verbatim to the AI
            // metadata call (see MetadataAnalysisService), so an unbounded
            // abstract is both a UX problem here and a token-cost problem
            // there.
            'desc' => 'required|string|max:5000',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            // Aligned by index with keywords[] — lets the server tell an
            // AI-accepted suggestion apart from a hand-typed keyword
            // (source of the 'extracted' tag is always re-derived
            // server-side, never trusted from here — see syncKeywords()).
            'keyword_sources' => 'nullable|array',
            'keyword_sources.*' => 'nullable|in:extracted,ai_suggested,manual',
            'sdg' => 'required|array|min:1',
            'sdg.*' => 'exists:sdgs,id',
            'visibility' => 'required|in:public,private',
            'file' => 'required|mimes:pdf|max:'.(config('research.max_upload_mb', 10) * 1024),
            // Echoed back from a prior /research/analyze call, purely for
            // audit — the server recomputes and verifies the fingerprint
            // against the actually-submitted title/abstract before trusting it.
            'ai_analysis_fingerprint' => 'nullable|string',
            'ai_model_used' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a PDF file.',
            'file.mimes' => 'Only PDF files are allowed. DOC and DOCX files are not accepted.',
            'file.max' => 'The PDF file must not exceed '.config('research.max_upload_mb', 10).' MB.',
            'desc.max' => 'The abstract must not exceed 5000 characters.',
            'adviser_id.required' => 'Please select a research adviser.',
            'adviser_id.exists' => 'Please select a valid research adviser.',
        ];
    }
}
