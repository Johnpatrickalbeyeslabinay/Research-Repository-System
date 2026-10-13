<?php

namespace App\Http\Controllers;

use App\Exceptions\AbstractGenerationException;
use App\Exceptions\AiQuotaExceededException;
use App\Exceptions\MetadataAnalysisException;
use App\Models\Sdg;
use App\Services\Ai\AbstractGenerationService;
use App\Services\Ai\MetadataAnalysisService;
use App\Support\SubmissionAiQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AI assistance in the submission form: keyword + SDG suggestions, and an
 * abstract draft for papers that have none. Deliberately separate from
 * StudentController, which owns the deterministic PDF pipeline
 * (validation/extraction) — this controller is the one place generative
 * AI calls happen in the submission flow. Both endpoints share the
 * student's daily SubmissionAiQuota; cached results don't count against it.
 */
class ResearchAnalysisController extends Controller
{
    public function __construct(
        private MetadataAnalysisService $metadataAnalysis,
        private AbstractGenerationService $abstractGeneration,
    ) {}

    public function analyze(Request $request)
    {
        // 'abstract' is capped to match StoreResearchRequest/UpdateResearchRequest's
        // 'desc' limit — this is the same field, just posted ahead of final
        // submission, and it goes straight into the AI prompt (see
        // MetadataAnalysisService::callOpenAi) so an unbounded value here is
        // also an unbounded token cost.
        $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string|max:5000',
            'fallback_excerpt' => 'nullable|string|max:5000',
            'existing_keywords' => 'nullable|array|max:30',
            'existing_keywords.*' => 'string|max:100',
        ]);

        try {
            $result = $this->metadataAnalysis->analyze(
                $request->input('title'),
                $request->input('abstract'),
                $request->input('fallback_excerpt'),
                $request->input('existing_keywords', [])
            );
        } catch (AiQuotaExceededException $e) {
            return $this->quotaExceeded($e);
        } catch (MetadataAnalysisException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // The service deals only in SDG *numbers* (the fixed UN 1-17
        // numbering baked into the prompt) — it never touches the
        // database. Resolving to this installation's actual sdgs.id rows
        // happens here, once, so the client only ever sees real row ids
        // and never has to assume id == number (see sdg-selector.blade.php
        // and the SdgSeeder note on why that assumption used to silently
        // break every suggestion).
        $idsByNumber = Sdg::whereIn('number', array_column($result['sdgs'], 'number'))
            ->pluck('id', 'number');

        $result['sdgs'] = array_values(array_filter(array_map(
            fn ($sdg) => isset($idsByNumber[$sdg['number']])
                ? $sdg + ['id' => $idsByNumber[$sdg['number']]]
                : null, // no row for this number — drop rather than emit a bogus id
            $result['sdgs']
        )));

        return response()->json(array_merge(
            ['success' => true],
            $result,
            ['quota' => SubmissionAiQuota::status(Auth::id())]
        ));
    }

    /**
     * Drafts an abstract for the PDF the student just picked. Only the
     * PDF's hash comes from the client — the paper excerpt was stored
     * server-side by StudentController::analyzePdf() (see
     * AbstractGenerationService's docblock for why).
     */
    public function generateAbstract(Request $request)
    {
        $request->validate([
            'pdf_hash' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'title' => 'required|string|max:255',
        ]);

        try {
            $result = $this->abstractGeneration->generate(
                $request->input('pdf_hash'),
                $request->input('title')
            );
        } catch (AiQuotaExceededException $e) {
            return $this->quotaExceeded($e);
        } catch (AbstractGenerationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(array_merge(
            ['success' => true],
            $result,
            ['quota' => SubmissionAiQuota::status(Auth::id())]
        ));
    }

    private function quotaExceeded(AiQuotaExceededException $e)
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'quota' => SubmissionAiQuota::status(Auth::id()),
        ], 429);
    }
}
