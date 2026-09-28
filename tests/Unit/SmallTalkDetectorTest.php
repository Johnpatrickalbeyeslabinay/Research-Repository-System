<?php

namespace Tests\Unit;

use App\Services\Chat\SmallTalk\SmallTalkDetector;
use App\Services\Chat\SmallTalk\SmallTalkMatch;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The small-talk lexicon, exhaustively.
 *
 * Extends Tests\TestCase rather than PHPUnit's directly because the fuzzy
 * matcher reads config('chat.smalltalk.*') — the same reason
 * QueryCondenserTest boots the framework while IntentDetectorTest does not.
 *
 * The neverSmallTalk provider is the important half of this file. Loosening
 * detection is how you save tokens; it is also how you accidentally answer a
 * real research question with "Hello!". Every guard therefore gets a named
 * case, so a future lexicon addition that breaks one fails with a message
 * saying which rule it broke.
 */
class SmallTalkDetectorTest extends TestCase
{
    private SmallTalkDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new SmallTalkDetector;
    }

    public static function pureGreetings(): array
    {
        return [
            'bare hi' => ['hi'],
            'with filler' => ['hi there'],
            'repeated letters' => ['hiii'],
            'long repeated letters' => ['helloooo'],
            'exclamation' => ['Hello!'],
            'trailing comma' => ['hey,'],
            'emoji only' => ['👋'],
            'greeting plus emoji' => ['hey!! 👋'],
            'uppercase' => ['HELLO'],
            'good morning' => ['good morning'],
            'good day with honorific' => ['good day sir'],
            'good evening maam' => ['good evening maam'],
            'yo' => ['yo'],
            'sup' => ['sup'],
            'whats up' => ['whats up'],
            'how are you' => ['how are you?'],
            'typo helo' => ['helo'],
            'typo hallo' => ['hallo'],
            // Users are at CSU Aparri in Cagayan — these are ordinary input.
            'tagalog kumusta' => ['kumusta'],
            'tagalog kumusta po' => ['kumusta po'],
            'tagalog shortened' => ['musta'],
            'tagalog magandang umaga' => ['magandang umaga'],
            'tagalog magandang umaga po' => ['magandang umaga po'],
            'tagalog magandang hapon' => ['Magandang hapon!'],
            'ilocano naimbag nga bigat' => ['naimbag nga bigat'],
        ];
    }

    #[DataProvider('pureGreetings')]
    public function test_it_recognises_a_greeting(string $message): void
    {
        $match = $this->detector->match($message);

        $this->assertTrue(
            $match->isGreeting(),
            "\"{$message}\" should be a zero-token greeting, but was categorised as {$match->category}."
        );
        $this->assertTrue($match->isZeroToken());
    }

    public static function pureAcknowledgements(): array
    {
        return [
            'thanks' => ['thanks'],
            'thank you' => ['thank you'],
            'thank you po' => ['thank you po'],
            'thx' => ['thx'],
            'ty' => ['ty'],
            'tagalog salamat' => ['salamat'],
            'tagalog maraming salamat' => ['maraming salamat'],
            'tagalog sige' => ['sige'],
            'tagalog sige po' => ['sige po'],
            'ok' => ['ok'],
            'okay' => ['okay'],
            'got it' => ['got it'],
            'noted' => ['noted'],
            'sounds good' => ['sounds good'],
            'appreciate it' => ['appreciate it'],
            'solo cool' => ['cool'],
            'solo great' => ['great'],
            'farewell bye' => ['bye'],
            'farewell see you' => ['see you'],
            'farewell paalam' => ['paalam'],
        ];
    }

    #[DataProvider('pureAcknowledgements')]
    public function test_it_recognises_an_acknowledgement(string $message): void
    {
        $match = $this->detector->match($message);

        $this->assertTrue(
            $match->isAcknowledgement(),
            "\"{$message}\" should be a zero-token acknowledgement, but was categorised as {$match->category}."
        );
    }

    /**
     * Each case names the guard it protects. A failure here is a real
     * question being answered with a canned greeting — much worse than the
     * wasted tokens the detector exists to avoid.
     */
    public static function neverSmallTalk(): array
    {
        return [
            'G1b hyphen is not a boundary' => ['hi-tech aquaculture methods'],
            'G1 greeting word inside a title' => ['Hello World study'],
            'G1 adjective plus noun' => ['good results'],
            'G4 solo-only word with content' => ['great findings on tilapia'],
            'G4 nice is an adjective here' => ['nice work by the authors'],
            'G4 cool describes water' => ['cool water species'],
            'G4 perfect describes attendance' => ['perfect attendance study'],
            'G7 a digit means something specific' => ['good 2024 results'],
            'G7 digits with ok' => ['ok 5 studies'],
            'G8 a quoted title' => ['is there a study called "Hello World"?'],
            'phrase contiguity' => ['how are the fish populations doing?'],
            'fuzzy floor: no' => ['no'],
            'fuzzy floor: of' => ['of'],
            'fuzzy floor: is' => ['is'],
            'fuzzy length cap' => ['helodermatidae'],
            'G6 too many tokens' => ['hi hello hey yo sup greetings thanks'],
            'a real question' => ['what research is available about tilapia'],
            'a methodology question' => ['what methodology did the seaweed study use'],
            'ok with a question mark is asking' => ['ok?'],
            'a name that starts like a greeting' => ['heyward et al findings'],
        ];
    }

    #[DataProvider('neverSmallTalk')]
    public function test_it_does_not_claim_a_real_message(string $message): void
    {
        $match = $this->detector->match($message);

        $this->assertFalse(
            $match->isZeroToken(),
            "\"{$message}\" was wrongly treated as small talk (category: {$match->category}). "
                .'A real question answered with a canned greeting is the worst outcome available here.'
        );
    }

    /**
     * The single biggest regression risk in loosening detection: a greeting
     * prefix must be stripped and the REAL question routed, never answered
     * with a bare hello.
     */
    public static function compoundMessages(): array
    {
        return [
            'comma boundary' => [
                'hi, what research is there about tilapia?',
                'what research is there about tilapia?',
            ],
            'tagalog prefix' => [
                'hello po, list repository available',
                'list repository available',
            ],
            'exclamation boundary' => [
                'good morning! how many studies are there?',
                'how many studies are there?',
            ],
            'no punctuation but a question opener' => [
                'hi what studies are available',
                'what studies are available',
            ],
            'honorific then question' => [
                'good day sir, who wrote the seaweed paper',
                'who wrote the seaweed paper',
            ],
        ];
    }

    #[DataProvider('compoundMessages')]
    public function test_a_greeting_prefix_is_stripped_and_the_question_survives(string $message, string $expected): void
    {
        $match = $this->detector->match($message);

        $this->assertFalse(
            $match->isZeroToken(),
            "\"{$message}\" carries a real question and must never be answered as small talk."
        );
        $this->assertTrue($match->hasStrippedPrefix());
        $this->assertSame($expected, $match->residual);
    }

    /**
     * The counterpart to the above: when the boundary does not look like a
     * greeting handoff, nothing is stripped and the message is passed through
     * completely untouched.
     */
    public function test_a_non_boundary_leaves_the_message_intact(): void
    {
        $message = 'hi-tech aquaculture methods';
        $match = $this->detector->match($message);

        $this->assertFalse($match->hasStrippedPrefix());
        $this->assertSame($message, $match->residual);
    }

    public static function unintelligibleMessages(): array
    {
        return [
            'punctuation only' => ['???'],
            'symbols' => ['!!!...'],
            'single character' => ['x'],
            'consonant mash with no vowel' => ['qwrtzxcvb'],
        ];
    }

    #[DataProvider('unintelligibleMessages')]
    public function test_gibberish_is_a_zero_token_path(string $message): void
    {
        $match = $this->detector->match($message);

        $this->assertTrue(
            $match->isZeroToken(),
            "\"{$message}\" should be handled without an API call."
        );
        $this->assertContains($match->category, [
            SmallTalkMatch::UNINTELLIGIBLE,
            SmallTalkMatch::GREETING,
        ]);
    }

    public function test_a_greeting_wins_over_an_acknowledgement_when_both_appear(): void
    {
        $this->assertTrue($this->detector->match('hi thanks')->isGreeting());
    }

    /**
     * Mash that survives the heuristic, documented rather than hidden.
     *
     * "asdkjhasd" contains vowels, so the conservative rule in
     * looksUnintelligible() does not fire and it costs a normal turn. Catching
     * it would need a consonant-run rule, which also flags real words like
     * "strengths" — refusing a real question is worse than one wasted turn, so
     * this is the deliberate trade, and this test pins it so a future change
     * to that rule is a visible decision.
     */
    public function test_vowel_bearing_mash_is_deliberately_not_claimed(): void
    {
        $this->assertFalse($this->detector->match('asdkjhasd')->isZeroToken());
    }

    public function test_fuzzy_matching_can_be_disabled(): void
    {
        // 'hllo' is one edit from 'hello' and is not itself in the lexicon.
        // (Not 'helllo' — normalization collapses the triple 'l' to 'hello',
        // which then matches exactly and never reaches the fuzzy path.)
        $this->assertTrue($this->detector->match('hllo')->isGreeting());

        config(['chat.smalltalk.fuzzy_enabled' => false]);

        // An exact lexicon entry still matches...
        $this->assertTrue($this->detector->match('hello')->isGreeting());

        // ...but the typo no longer does.
        $this->assertFalse($this->detector->match('hllo')->isZeroToken());
    }

    public function test_normalization_collapses_letter_runs_without_damaging_words(): void
    {
        // No English or Filipino word has three identical consecutive letters,
        // so this collapse is lossless.
        $this->assertSame('hi', $this->detector->normalize('hiiiii'));
        $this->assertSame('aquaculture', $this->detector->normalize('aquaculture'));
        $this->assertSame('seaweed', $this->detector->normalize('seaweed'));
    }
}
