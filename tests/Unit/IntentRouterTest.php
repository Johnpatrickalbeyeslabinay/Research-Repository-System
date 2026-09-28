<?php

namespace Tests\Unit;

use App\Services\Chat\IntentDetector;
use App\Services\Chat\Routing\Classifiers\CatalogClassifier;
use App\Services\Chat\Routing\Classifiers\ExternalLiteratureClassifier;
use App\Services\Chat\Routing\Classifiers\IdentityClassifier;
use App\Services\Chat\Routing\Classifiers\OutOfScopeClassifier;
use App\Services\Chat\Routing\Classifiers\PlatformClassifier;
use App\Services\Chat\Routing\Classifiers\SmallTalkClassifier;
use App\Services\Chat\Routing\Intent;
use App\Services\Chat\Routing\IntentRouter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Routing precedence, asserted rather than inferred from registration order.
 *
 * Extends Tests\TestCase because the router is resolved from the container
 * (the classifier chain is a tagged binding). Every assertion message names
 * the CONSEQUENCE of getting the order wrong, so a failure tells you what
 * breaks in production rather than just which enum case mismatched.
 */
class IntentRouterTest extends TestCase
{
    private IntentRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = app(IntentRouter::class);
    }

    public function test_the_resolved_order_is_by_priority_not_registration(): void
    {
        $order = array_column($this->router->resolvedOrder(), 'class');

        $this->assertSame([
            SmallTalkClassifier::class,
            IdentityClassifier::class,
            PlatformClassifier::class,
            OutOfScopeClassifier::class,
            ExternalLiteratureClassifier::class,
            CatalogClassifier::class,
        ], $order);
    }

    public function test_priorities_are_unique(): void
    {
        $priorities = array_column($this->router->resolvedOrder(), 'priority');

        $this->assertSame(
            $priorities,
            array_unique($priorities),
            'Two classifiers share a priority, so their relative order is whatever the container happened to produce.'
        );
    }

    /**
     * @return array<string, array{0: string, 1: Intent, 2: string}>
     */
    public static function routingCases(): array
    {
        return [
            'a bare greeting' => [
                'hi', Intent::SmallTalk,
                'A greeting must never reach retrieval.',
            ],
            'a tagalog greeting' => [
                'magandang umaga', Intent::SmallTalk,
                'Tagalog greetings are ordinary input at CSU Aparri and must be free.',
            ],
            'thanks' => [
                'salamat', Intent::Acknowledgement,
                'An acknowledgement must never reach retrieval.',
            ],
            'gibberish' => [
                '???', Intent::Unintelligible,
                'Punctuation-only input has nothing to retrieve.',
            ],

            'who are you' => [
                'who are you?', Intent::Identity,
                'Identity must beat everything else: this used to run full retrieval and a ~2,500-token answer call to produce a constant.',
            ],
            'are you chatgpt' => [
                'are you chatgpt', Intent::Identity,
                'Identity question, answerable from a constant.',
            ],
            'what can you do' => [
                'what can you do?', Intent::Identity,
                'Identity must precede Platform, whose "what can i do here" pattern would otherwise claim this and spend a real call.',
            ],

            'how do i submit' => [
                'how do I submit my research?', Intent::Platform,
                'A platform question is answered from PLATFORM_INFO, without retrieval.',
            ],
            'what is this system' => [
                'what is this system?', Intent::Platform,
                'A platform question.',
            ],

            'off topic recommendation' => [
                'recommend a good restaurant', Intent::OutOfScope,
                'OutOfScope must precede Catalog: this matches IntentDetector\'s /(recommend|suggest)/ and used to spend an LLM call listing fisheries papers in reply to a question about dinner.',
            ],
            'off topic weather' => [
                'what is the weather today', Intent::OutOfScope,
                'Off-domain subject with no research term present.',
            ],
            'off topic sport' => [
                'who won the nba finals', Intent::OutOfScope,
                'Off-domain subject with no research term present.',
            ],

            'bare inventory count' => [
                'how many studies are in the repository?', Intent::CatalogCount,
                'The answer is CatalogResult::$total, a number PHP already holds — it should cost nothing.',
            ],
            'bare inventory list' => [
                'list repository available', Intent::Catalog,
                'An inventory question needs the catalog, not the count shortcut.',
            ],
            'topical recommendation' => [
                'recommend papers related to fish', Intent::Catalog,
                'A domain recommendation is a catalog question, not out of scope.',
            ],

            'a methodology question' => [
                'what methodology did the seaweed study use?', Intent::ResearchQa,
                'Content questions must win over inventory patterns and reach full retrieval.',
            ],
            'a findings question' => [
                'what were the findings about tilapia growth?', Intent::ResearchQa,
                'A research question.',
            ],
            'an unclaimed question' => [
                'tilapia pond salinity in Aparri', Intent::ResearchQa,
                'Anything unclaimed is a research question — the router default.',
            ],
        ];
    }

    #[DataProvider('routingCases')]
    public function test_it_routes_to_the_expected_intent(string $message, Intent $expected, string $why): void
    {
        $routed = $this->router->route($message);

        $this->assertSame(
            $expected,
            $routed->intent,
            "\"{$message}\" routed to {$routed->intent->value} (via {$routed->classifier}), expected {$expected->value}. {$why}"
        );
    }

    /**
     * The purity contract in TurnClassifier, enforced. The whole value of
     * routing before retrieval is that it costs nothing — a classifier that
     * queries or calls out is one that can be slow or fail on a turn that was
     * supposed to be free.
     */
    public function test_routing_touches_neither_the_database_nor_the_network(): void
    {
        Http::preventStrayRequests();
        DB::enableQueryLog();
        DB::flushQueryLog();

        foreach (array_keys(self::routingCases()) as $name) {
            [$message] = self::routingCases()[$name];
            $this->router->route($message);
        }

        $this->assertEmpty(
            DB::getQueryLog(),
            'A classifier issued a database query. Classification runs before retrieval precisely so it is free.'
        );
        Http::assertNothingSent();
    }

    /** A greeting prefix is stripped and the real question is routed. */
    public function test_a_compound_message_routes_on_the_question(): void
    {
        $routed = $this->router->route('hi, list repository available');

        $this->assertSame(Intent::Catalog, $routed->intent);
        $this->assertTrue($routed->isCompound());
        $this->assertSame('list repository available', $routed->question);
        $this->assertSame('hi, list repository available', $routed->rawQuestion);
    }

    public function test_a_pinned_conversation_never_routes_to_catalog(): void
    {
        $routed = $this->router->route('list repository available', isPinnedConversation: true);

        $this->assertSame(
            Intent::ResearchQa,
            $routed->intent,
            'A pinned chat is scoped to one document — there is nothing to browse.'
        );
    }

    public function test_a_pinned_conversation_is_never_refused_as_off_topic(): void
    {
        $routed = $this->router->route('is this about restaurant sourcing', isPinnedConversation: true);

        $this->assertSame(
            Intent::ResearchQa,
            $routed->intent,
            'Inside a document-scoped chat, let retrieval answer — never refuse.'
        );
    }

    /**
     * The OutOfScope safety rule: an off-domain word alongside any domain term
     * must NOT be refused. Refusing a real research question is far worse than
     * answering an off-topic one.
     */
    public static function offDomainWordsWithDomainContext(): array
    {
        return [
            ['is there research on restaurant fish sourcing'],
            ['any studies about weather effects on ponds'],
            ['what papers use python for data analysis'],
            ['thesis about election coverage of fisheries policy'],
        ];
    }

    /**
     * A code-writing request is genuinely off-topic, but it is NOT refused —
     * and that is deliberate.
     *
     * Detecting it would mean treating language names as off-domain signals,
     * and this repository's corpus is IT capstone and thesis work (document
     * management systems, AI in education, GPS tracking). Under that lexicon
     * "what about python?" — a legitimate question about the corpus — would be
     * declined. Refusing a real research question is worse than answering an
     * off-topic one, so these fall through to the research pipeline, find no
     * evidence, and get the honest no-evidence reply. That costs one embedding
     * call and cannot mis-refuse anyone.
     */
    public function test_a_code_request_is_not_refused_because_language_names_are_on_topic_here(): void
    {
        $this->assertNotSame(
            Intent::OutOfScope,
            $this->router->route('write me a python script')->intent,
        );

        // The reason why: this is a real question about the corpus.
        $this->assertNotSame(
            Intent::OutOfScope,
            $this->router->route('is there research using python for data analysis')->intent,
        );
    }

    #[DataProvider('offDomainWordsWithDomainContext')]
    public function test_an_off_domain_word_with_a_domain_term_is_not_refused(string $message): void
    {
        $routed = $this->router->route($message);

        $this->assertNotSame(
            Intent::OutOfScope,
            $routed->intent,
            "\"{$message}\" mentions the repository's own subject matter and must never be refused as off-topic."
        );
    }

    /**
     * The router must agree with IntentDetector on every case that class
     * already covers, so wrapping it cannot silently change behaviour.
     */
    public function test_it_agrees_with_the_existing_intent_detector(): void
    {
        $detector = new IntentDetector;

        $catalogPhrasings = [
            'list repository available',
            'what research is available',
            'what topics are covered',
            'suggest studies about aquaculture',
            'which studies are related to SDG 14',
        ];

        foreach ($catalogPhrasings as $message) {
            $this->assertTrue($detector->isCatalogQuestion($message));

            $routed = $this->router->route($message);

            $this->assertContains(
                $routed->intent,
                [Intent::Catalog, Intent::CatalogCount],
                "IntentDetector calls \"{$message}\" a catalog question but the router routed it to {$routed->intent->value}."
            );
        }
    }
}
