<?php

namespace App\Services\Chat\Routing\Classifiers;

use App\Services\Chat\IntentDetector;
use App\Services\Chat\Routing\ClassificationInput;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Chat\Routing\TurnClassifier;

/**
 * "What research is available", "recommend papers about X" — document-level
 * questions that chunk retrieval structurally cannot answer, because no chunk
 * contains a list of the repository's contents.
 *
 * A thin adapter over the existing IntentDetector, which is NOT modified: its
 * inventory/recommendation/content pattern sets are already covered by
 * IntentDetectorTest, and its rule that content questions win over inventory
 * ones is load-bearing.
 *
 * It also splits off one special case. "How many studies are in the
 * repository?" is a bare inventory question whose entire answer is
 * CatalogResult::$total — a number PHP already holds. Today it spends a
 * ~600-token catalog prompt plus a 450-token output ceiling to restate that
 * number in a sentence. Given output tokens bill at 4x input on gpt-4o-mini,
 * and this is one of the most common questions a repository gets, it is worth
 * its own zero-token intent.
 */
final class CatalogClassifier implements TurnClassifier
{
    /**
     * Counting questions. Requires an explicit counting phrase — "what
     * research is available" must stay a normal catalog turn, because the user
     * wants the list, not the size of it.
     */
    private const COUNT_PATTERNS = [
        '/\bhow\s+many\b/i',
        '/\bhow\s+much\s+research\b/i',
        '/\b(number|count|total)\s+of\s+(research|researches|studies|papers|documents|theses)\b/i',
        '/\b(research|studies|papers|documents)\s+count\b/i',
        '/\bilan\b/i', // Tagalog "how many"
    ];

    public function __construct(private IntentDetector $intents) {}

    public function priority(): int
    {
        return 60;
    }

    public function classify(ClassificationInput $input): ?RoutedIntent
    {
        // A pinned chat is already scoped to one document — there is nothing
        // to browse. This mirrors the stud_file_id === null guard the
        // controller's catalog branch has always had.
        if ($input->isPinnedConversation) {
            return null;
        }

        if (! $this->intents->isCatalogQuestion($input->question)) {
            return null;
        }

        $isBareInventory = $this->intents->isBareInventory($input->question);

        // Only a BARE inventory count qualifies for the free path. "How many
        // studies are there about tilapia" is topical: the count depends on
        // retrieval, so it stays a normal catalog turn.
        if ($isBareInventory && $this->isCountQuestion($input->question)) {
            return new RoutedIntent(
                intent: Intent::CatalogCount,
                question: $input->question,
                rawQuestion: $input->rawQuestion,
                strippedSmallTalk: $input->strippedSmallTalk,
                classifier: 'catalog_count',
                signals: ['is_bare_inventory' => true],
            );
        }

        return new RoutedIntent(
            intent: Intent::Catalog,
            question: $input->question,
            rawQuestion: $input->rawQuestion,
            strippedSmallTalk: $input->strippedSmallTalk,
            classifier: 'catalog',
            signals: ['is_bare_inventory' => $isBareInventory],
        );
    }

    private function isCountQuestion(string $question): bool
    {
        foreach (self::COUNT_PATTERNS as $pattern) {
            if (preg_match($pattern, $question)) {
                return true;
            }
        }

        return false;
    }
}
