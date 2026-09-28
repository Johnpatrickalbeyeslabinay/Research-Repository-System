<?php

namespace App\Services\Chat\Routing\Classifiers;

use App\Services\Chat\IntentDetector;
use App\Services\Chat\Routing\ClassificationInput;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Chat\Routing\TurnClassifier;

/**
 * An explicit request for research from OUTSIDE this repository.
 *
 * Priority 50 puts this ahead of CatalogClassifier, which would otherwise claim
 * "find research about tilapia on the internet" via its
 * /\bfind( me| us)?\b...\b(research|studies)\b/ pattern and answer an explicit
 * request for outside literature from two internal rows.
 *
 * Note this classifier does NOT check whether the feature is enabled — a
 * classifier must stay pure (no config-driven behaviour would break that, but
 * no DB or HTTP either), and more importantly the intent is a fact about the
 * message regardless of whether we can act on it. The handler checks
 * availability and falls back to an ordinary research turn when the feature is
 * off, so a disabled deployment still answers the question from the local
 * corpus rather than refusing it.
 */
final class ExternalLiteratureClassifier implements TurnClassifier
{
    /**
     * Phrasings that unambiguously ask to look beyond this repository. Anchored
     * on explicit "outside/elsewhere/published" language rather than on topic
     * words, because almost every question here is about published research —
     * what distinguishes these is the request to leave the collection.
     */
    private const PATTERNS = [
        '/\b(outside|beyond|not in|other than)\b[^.?!]{0,30}\b(this |the )?(repository|repo|collection|database|system|school|university|campus)\b/i',
        '/\b(other|another|different)\b[^.?!]{0,20}\b(universit|institution|school|college|countr)\w*\b/i',
        '/\b(international|foreign|global|worldwide|abroad|overseas)\b[^.?!]{0,30}\b(research|stud(y|ies)|papers?|literature|journals?)\b/i',
        '/\b(published|existing|external|outside)\b[^.?!]{0,20}\b(literature|research|stud(y|ies)|papers?)\b/i',
        '/\b(literature review|related literature|review of literature)\b/i',
        '/\bon (the )?(internet|web|google|scholar|openalex|pubmed|crossref)\b/i',
        '/\b(elsewhere|anywhere else|somewhere else)\b/i',
        '/\bnot (just |only )?(from|in) (this|our) (repository|collection|school|database)\b/i',
    ];

    public function __construct(private IntentDetector $intents) {}

    public function priority(): int
    {
        return 50;
    }

    public function classify(ClassificationInput $input): ?RoutedIntent
    {
        // A pinned chat is scoped to one specific paper. Searching the wider
        // world from inside it is both off-purpose and a privacy risk: the
        // condensed topic could carry a private paper's title off-site.
        if ($input->isPinnedConversation) {
            return null;
        }

        // A bare inventory question ("list repository available") has no topic
        // to search for — sending it to OpenAlex would query the literal words
        // "list repository available".
        if ($this->intents->isBareInventory($input->question)) {
            return null;
        }

        if (! $this->hasSearchableTopic($input->question)) {
            return null;
        }

        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $input->question)) {
                return new RoutedIntent(
                    intent: Intent::ExternalLiterature,
                    question: $input->question,
                    rawQuestion: $input->rawQuestion,
                    strippedSmallTalk: $input->strippedSmallTalk,
                    classifier: 'external_literature',
                    signals: ['matched_pattern' => $pattern],
                );
            }
        }

        return null;
    }

    /**
     * At least two content-bearing words of 3+ characters. Without a real topic
     * there is nothing to send outward, and "any research elsewhere?" should
     * fall through to the normal pipeline rather than querying an index with
     * two stopwords.
     */
    private function hasSearchableTopic(string $question): bool
    {
        $stripped = (string) preg_replace(
            '/\b(is|are|there|any|other|research|researches|stud(y|ies)|papers?|literature|about|from|outside|beyond|this|the|in|on|of|and|or|published|existing|external|international|elsewhere)\b/i',
            ' ',
            $question,
        );

        preg_match_all('/[\p{L}]{3,}/u', $stripped, $matches);

        return count(array_unique($matches[0])) >= 2;
    }
}
