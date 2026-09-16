<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // StudentController::update() scopes to the owner's own record
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'research_type' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),
            'adviser_id' => 'required|exists:users,id',
            'researchers' => 'nullable|array|max:20',
            'researchers.*.name' => 'required|string|max:255',
            'researchers.*.user_id' => 'nullable|exists:users,id',
            'desc' => 'required|string|max:5000',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'keyword_sources' => 'nullable|array',
            'keyword_sources.*' => 'nullable|in:extracted,ai_suggested,manual',
            'sdg' => 'required|array|min:1',
            'sdg.*' => 'exists:sdgs,id',
            'visibility' => 'required|in:public,private',
            'file' => 'nullable|mimes:pdf|max:'.(config('research.max_upload_mb', 10) * 1024),
            'ai_analysis_fingerprint' => 'nullable|string',
            'ai_model_used' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Only PDF files are allowed. DOC and DOCX files are not accepted.',
            'file.max' => 'The PDF file must not exceed '.config('research.max_upload_mb', 10).' MB.',
            'desc.max' => 'The abstract must not exceed 5000 characters.',
            'adviser_id.required' => 'Please select a research adviser.',
            'adviser_id.exists' => 'Please select a valid research adviser.',
        ];
    }
}
