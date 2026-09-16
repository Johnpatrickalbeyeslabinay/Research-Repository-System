<?php

namespace App\Http\Controllers;

use App\Exceptions\MetadataAnalysisException;
use App\Models\Sdg;
use App\Services\Ai\MetadataAnalysisService;
use Illuminate\Http\Request;

/**
 * AI-assisted metadata suggestions (keywords + SDGs) from a research title
 * and abstract only. Deliberately separate from StudentController, which
 * owns the deterministic PDF pipeline (validation/extraction) — this
 * controller is the one place a generative AI call happens in the
 * submission flow.
 */
class ResearchAnalysisController extends Controller
{
    public function __construct(private MetadataAnalysisService $metadataAnalysis) {}

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
        ]);

        try {
            $result = $this->metadataAnalysis->analyze(
                $request->input('title'),
                $request->input('abstract'),
                $request->input('fallback_excerpt')
            );
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

        return response()->json(array_merge(['success' => true], $result));
    }
}
