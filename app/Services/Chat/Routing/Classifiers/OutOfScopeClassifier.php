<?php

namespace App\Services\Chat\Routing\Classifiers;

use App\Services\Chat\Routing\ClassificationInput;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\RoutedIntent;
use App\Services\Chat\Routing\TurnClassifier;

/**
 * Clearly off-domain requests, answered for zero tokens.
 *
 * This exists because of a live bug: "recommend a good restaurant" matches
 * IntentDetector's /\b(recommend|suggest)\b/i, so it reached
 * CatalogService::browse() and spent a real LLM call listing fisheries papers
 * in reply to a question about dinner. Priority 40 puts this ahead of
 * CatalogClassifier so that can no longer happen.
 *
 * TWO CONDITIONS, both required — this is the whole safety design. Refusing a
 * real research question is much worse than answering an off-topic one, so it
 * fires only when an off-domain term is present AND no domain term is present
 * anywhere in the message. "recommend a good restaurant" has no domain term and
 * is claimed; "is there research on restaurant fish sourcing" carries
 * "research" and is not.
 *
 * The lexicon is kept small and unambiguous on purpose. Anything that could
 * plausibly appear in a fisheries or aquaculture paper is left out entirely —
 * "fish", "water", "food", "market", "temperature" and the like are absent by
 * design, not by omission.
 */
final class OutOfScopeClassifier implements TurnClassifier
{
    /**
     * Subjects this repository could not hold research about in any reading.
     * Word-boundary matched, so "cooking" does not match via "cook".
     */
    private const OFF_DOMAIN = [
        // Food and dining as consumption, not as fisheries science.
        'restaurant', 'restaurants', 'cafe', 'karaoke', 'recipe', 'recipes',
        // Everyday chit-chat subjects.
        'weather', 'forecast', 'horoscope', 'lottery', 'jokes',
        'boyfriend', 'girlfriend', 'crush', 'dating',
        // Entertainment and sport.
        'netflix', 'movie', 'movies', 'anime', 'basketball', 'nba', 'football',
        'volleyball', 'boxing', 'videogame', 'videogames', 'roblox', 'minecraft',
        // Off-domain help requests people try on any chatbot.
        //
        // Programming languages are DELIBERATELY ABSENT. An earlier version
        // listed javascript/python/php/html/excel here, which was wrong for
        // this repository: it holds IT capstone and thesis work — document
        // management systems, AI in education, GPS tracking — so those are
        // on-topic subjects, not off-domain ones. Refusing "what about python?"
        // would mean declining a question about the corpus itself.
        'homework', 'essay', 'poem', 'lovelife',
        'bitcoin', 'crypto', 'stocks', 'forex',
        'president', 'election', 'politics',
    ];

    /**
     * Any of these makes the message plausibly about this repository, and the
     * classifier stands down. Generous on purpose — a false negative here just
     * means the turn routes normally, which is the status quo.
     */
    private const DOMAIN_TERMS = [
        'research', 'researches', 'study', 'studies', 'thesis', 'theses',
        'paper', 'papers', 'publication', 'publications', 'journal',
        'repository', 'repositories', 'abstract', 'abstracts',
        'author', 'authors', 'adviser', 'advisor', 'citation', 'cite',
        'methodology', 'method', 'methods', 'findings', 'finding',
        'results', 'conclusion', 'conclusions', 'recommendation', 'recommendations',
        'sdg', 'sdgs',
        // Marine / fisheries — the corpus holds at least one seaweed
        // diversity survey (Gonzaga, Cagayan).
        'fisheries', 'fishery', 'aquaculture', 'aquatic', 'marine',
        'seaweed', 'tilapia', 'milkfish', 'bangus', 'mangrove',
        'pond', 'ponds', 'hatchery', 'intertidal', 'species', 'diversity',
        // Information technology — what most of this corpus actually is:
        // document management, web repositories, AI in education, GPS
        // tracking. These were missing entirely, so an IT question with an
        // incidental off-domain word could have been refused.
        'system', 'systems', 'software', 'application', 'app', 'web',
        'website', 'database', 'digital', 'online', 'platform',
        'document', 'documents', 'archive', 'archives', 'records',
        'tracking', 'gps', 'monitoring', 'automation', 'automated',
        'artificial', 'intelligence', 'machine', 'learning', 'algorithm',
        'cloud', 'computing', 'security', 'privacy', 'access',
        'literacy', 'education', 'students', 'student', 'academic',
        'management', 'information', 'technology', 'efficiency',
        // Institutional / process vocabulary.
        'csu', 'aparri', 'cagayan', 'capstone',
        'submission', 'submit', 'upload', 'approve', 'approval', 'published',
        'keyword', 'keywords', 'year', 'sample', 'data', 'respondents',
    ];

    public function priority(): int
    {
        return 40;
    }

    public function classify(ClassificationInput $input): ?RoutedIntent
    {
        // A pinned conversation is explicitly about one paper. Whatever was
        // asked, treat it as being about that paper and let retrieval decide —
        // never refuse inside a document-scoped chat.
        if ($input->isPinnedConversation) {
            return null;
        }

        $question = mb_strtolower($input->question);

        if ($this->containsAny($question, self::DOMAIN_TERMS)) {
            return null;
        }

        $matched = $this->firstMatch($question, self::OFF_DOMAIN);

        if ($matched === null) {
            return null;
        }

        return new RoutedIntent(
            intent: Intent::OutOfScope,
            question: $input->question,
            rawQuestion: $input->rawQuestion,
            strippedSmallTalk: $input->strippedSmallTalk,
            classifier: 'out_of_scope',
            signals: ['matched_term' => $matched],
        );
    }

    /**
     * @param  string[]  $terms
     */
    private function containsAny(string $haystack, array $terms): bool
    {
        return $this->firstMatch($haystack, $terms) !== null;
    }

    /**
     * @param  string[]  $terms
     */
    private function firstMatch(string $haystack, array $terms): ?string
    {
        foreach ($terms as $term) {
            if (preg_match('/\b'.preg_quote($term, '/').'\b/u', $haystack)) {
                return $term;
            }
        }

        return null;
    }
}
