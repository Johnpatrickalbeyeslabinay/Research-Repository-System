<?php

namespace App\Services\Chat\SmallTalk;

/**
 * Decides, for zero tokens, whether a message is small talk.
 *
 * Replaces GreetingDetector's anchored regexes
 * (/^(hi+|hello+|hey+|...)[\s!.,]*$/), which only matched a greeting that
 * was the ENTIRE message with nothing else at all. Everything below missed
 * that anchor and instead cost a query embedding plus a ~2,500-token answer
 * call, to produce a reply about the evidence not covering a greeting:
 *
 *     "hi there"   "hello po"   "good day sir"   "hey!! 👋"
 *     "helo"       "hiii"       "kamusta"        "magandang umaga"
 *
 * The approach is a token SET rather than a whole-string anchor: tokenize,
 * then treat the message as small talk only when EVERY token belongs to the
 * lexicon (SmallTalkLexicon). That keeps an anchor's tight false-positive
 * behaviour — one unrecognised word and it is a real question — while
 * accepting filler, particles, punctuation and emoji.
 *
 * The guard that matters most is the residual rule. "hi, what research is
 * there about tilapia?" is NOT small talk; it is a greeting prefix on a real
 * question, and answering it with "Hello!" would be a far worse bug than the
 * wasted tokens this class exists to save. So a leading greeting span is
 * reported separately (SmallTalkMatch::prefixOnly) and the remainder is what
 * gets routed.
 */
class SmallTalkDetector
{
    /**
     * Never small talk beyond these bounds, whatever the lexicon says. An
     * outer limit so a future lexicon mistake cannot swallow a real question.
     */
    private const MAX_TOKENS = 6;

    private const MAX_CHARS = 80;

    /**
     * Emoji, pictographs, variation selectors and ZWJ. Stripped before
     * tokenizing, but remembered: a message of nothing but 👋 IS a greeting.
     */
    private const EMOJI_PATTERN = '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F1E6}-\x{1F1FF}\x{200D}\x{2B00}-\x{2BFF}\x{2190}-\x{21FF}\x{2B50}\x{2757}]/u';

    public function match(string $message): SmallTalkMatch
    {
        $original = trim($message);

        if ($original === '') {
            return SmallTalkMatch::wholeMessage(SmallTalkMatch::UNINTELLIGIBLE, 'empty');
        }

        $hadEmoji = (bool) preg_match(self::EMOJI_PATTERN, $original);
        $normalized = $this->normalize($original);
        $tokens = $this->tokenize($normalized);

        // Nothing but emoji or punctuation. A lone wave is a greeting; a lone
        // "???" is not, and is left to the caller as unintelligible.
        if (empty($tokens)) {
            return $hadEmoji
                ? SmallTalkMatch::wholeMessage(SmallTalkMatch::GREETING, 'emoji')
                : SmallTalkMatch::wholeMessage(SmallTalkMatch::UNINTELLIGIBLE, 'no_tokens');
        }

        // G7: a digit anywhere means this is about something specific —
        // "good 2024 results", "ok 5 studies".
        foreach ($tokens as $token) {
            if (preg_match('/\d/', $token['text'])) {
                return SmallTalkMatch::none($original);
            }
        }

        // G8: a quoted span is a title or an exact phrase, never chatter —
        // 'is there a study called "Hello World"?'
        if (preg_match('/["“”]/u', $original)) {
            return SmallTalkMatch::none($original);
        }

        [$spanLength, $category, $method] = $this->longestLeadingSpan($tokens);

        if ($spanLength === 0) {
            return $this->looksUnintelligible($original, $tokens)
                ? SmallTalkMatch::wholeMessage(SmallTalkMatch::UNINTELLIGIBLE, 'heuristic')
                : SmallTalkMatch::none($original);
        }

        $isWholeMessage = $spanLength === count($tokens);

        if ($isWholeMessage) {
            // G6: the outer bound. A "greeting" of seven words or eighty
            // characters is almost certainly something else.
            if (count($tokens) > self::MAX_TOKENS || mb_strlen($original) > self::MAX_CHARS) {
                return SmallTalkMatch::none($original);
            }

            // A trailing '?' makes this a question even when every word is in
            // the lexicon — "ok?" is asking something, "ok" is agreeing.
            // ("how are you?" is exempt: it is a greeting phrase that simply
            // happens to be interrogative.)
            if (str_contains($original, '?') && $category !== SmallTalkMatch::GREETING) {
                return SmallTalkMatch::none($original);
            }

            return SmallTalkMatch::wholeMessage($category, $method);
        }

        // A span was matched but content follows. Only treat it as a strippable
        // greeting prefix when the boundary looks like one (G1b) — otherwise
        // leave the message completely untouched.
        $residual = $this->residualAfter($original, $tokens, $spanLength);

        if ($residual === null) {
            return SmallTalkMatch::none($original);
        }

        // Acknowledgement prefixes are not stripped: "thanks, now what about
        // tilapia" reads fine routed whole, and stripping risks mangling a
        // question that opens with an ordinary word.
        if ($category !== SmallTalkMatch::GREETING) {
            return SmallTalkMatch::none($original);
        }

        return SmallTalkMatch::prefixOnly($category, $residual, $method);
    }

    /**
     * Whether a message is input noise rather than a question.
     *
     * Deliberately CONSERVATIVE, and narrower than it could be. This path
     * refuses to answer, so a false positive means telling a student their
     * real question was gibberish — strictly worse than the wasted tokens the
     * detector exists to save. So it only fires on cases with no plausible
     * reading as a question:
     *
     *   - a single character ("x", "?")
     *   - a token of 5+ letters containing no vowel at all ("qwrtzxcvb")
     *
     * Note what this does NOT catch: "asdkjhasd" has vowels, so it falls
     * through to the normal pipeline and costs a turn. Catching it would need
     * either a consonant-run rule — which also flags real words like
     * "strengths" — or a dictionary, which this project has no room for. An
     * occasional wasted turn on mash is the right trade against ever refusing
     * a real question.
     *
     * @param  array<int, array{text: string, start: int, end: int}>  $tokens
     */
    private function looksUnintelligible(string $original, array $tokens): bool
    {
        $stripped = (string) preg_replace('/[^\p{L}\p{N}]/u', '', $original);

        if (mb_strlen($stripped) <= 1) {
            return true;
        }

        // A lone word with no vowel is not a word. Requires exactly one token,
        // so "dna rna" and other legitimate consonant-only abbreviations in a
        // real sentence are untouched.
        if (count($tokens) === 1) {
            $word = $tokens[0]['text'];

            if (mb_strlen($word) >= 5 && ! preg_match('/[aeiou]/i', $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * lowercase, normalize quotes/dashes, strip emoji, and collapse letter
     * runs. The run collapse (/(\p{L})\1{2,}/u) is lossless for real words:
     * no English or Filipino word has three identical consecutive letters, so
     * "hiii" -> "hi" and "helloooo" -> "hello" without endangering anything.
     */
    public function normalize(string $message): string
    {
        $s = mb_strtolower(trim($message));

        $s = str_replace(['’', '‘', '“', '”', '–', '—'], ["'", "'", '"', '"', '-', '-'], $s);
        $s = (string) preg_replace(self::EMOJI_PATTERN, ' ', $s);
        $s = (string) preg_replace('/(\p{L})\1{2,}/u', '$1', $s);

        return trim((string) preg_replace('/\s+/', ' ', $s));
    }

    /**
     * Tokens with byte offsets into the normalized string. Offsets are what
     * make prefix stripping possible without reconstructing the message —
     * chat_messages.content must keep the user's literal input, since the
     * widget echoes back what they typed.
     *
     * @return array<int, array{text: string, start: int, end: int}>
     */
    private function tokenize(string $normalized): array
    {
        preg_match_all("/[\p{L}\p{N}']+/u", $normalized, $matches, PREG_OFFSET_CAPTURE);

        $tokens = [];

        foreach ($matches[0] as [$text, $start]) {
            $tokens[] = [
                'text' => $text,
                'start' => $start,
                'end' => $start + strlen($text),
            ];
        }

        return $tokens;
    }

    /**
     * The longest run of tokens from the start that is entirely small talk.
     *
     * @param  array<int, array{text: string, start: int, end: int}>  $tokens
     * @return array{0: int, 1: string, 2: string} [span length, category, method]
     */
    private function longestLeadingSpan(array $tokens): array
    {
        $words = array_column($tokens, 'text');
        $total = count($words);

        $i = 0;
        $category = null;
        $method = 'lexicon';
        $isSoloCandidate = $total === 1;

        while ($i < $total) {
            $word = $words[$i];

            if ($phrase = $this->matchPhraseAt($words, $i)) {
                [$length, $phraseCategory] = $phrase;
                $category = $this->strongerCategory($category, $phraseCategory);
                $method = 'phrase';
                $i += $length;

                continue;
            }

            if (in_array($word, SmallTalkLexicon::GREETING_UNIGRAMS, true)) {
                $category = $this->strongerCategory($category, SmallTalkMatch::GREETING);
                $i++;

                continue;
            }

            if (in_array($word, SmallTalkLexicon::ACK_UNIGRAMS, true)) {
                $category = $this->strongerCategory($category, SmallTalkMatch::ACKNOWLEDGEMENT);
                $i++;

                continue;
            }

            if (in_array($word, SmallTalkLexicon::FAREWELL_UNIGRAMS, true)) {
                $category = $this->strongerCategory($category, SmallTalkMatch::FAREWELL);
                $i++;

                continue;
            }

            // G4: "cool", "nice", "great" are ordinary adjectives in a
            // research question, so they count only as the entire message.
            if ($isSoloCandidate && in_array($word, SmallTalkLexicon::ACK_SOLO_ONLY, true)) {
                $category = $this->strongerCategory($category, SmallTalkMatch::ACKNOWLEDGEMENT);
                $i++;

                continue;
            }

            // Particles ride along, but only once something real has matched:
            // a message opening with "please" alone is not a greeting.
            if ($category !== null && in_array($word, SmallTalkLexicon::PARTICLES, true)) {
                $i++;

                continue;
            }

            if ($category === null && $this->fuzzyGreeting($word, $total)) {
                $category = SmallTalkMatch::GREETING;
                $method = 'fuzzy';
                $i++;

                continue;
            }

            break;
        }

        return [$category === null ? 0 : $i, $category ?? SmallTalkMatch::NONE, $method];
    }

    /**
     * A greeting beats an acknowledgement when a message contains both
     * ("hi thanks") — the opening move sets the tone of the reply.
     */
    private function strongerCategory(?string $current, string $incoming): string
    {
        if ($current === SmallTalkMatch::GREETING || $incoming === SmallTalkMatch::GREETING) {
            return SmallTalkMatch::GREETING;
        }

        return $current ?? $incoming;
    }

    /**
     * @param  string[]  $words
     * @return array{0: int, 1: string}|null [tokens consumed, category]
     */
    private function matchPhraseAt(array $words, int $offset): ?array
    {
        $sets = [
            SmallTalkMatch::GREETING => SmallTalkLexicon::GREETING_PHRASES,
            SmallTalkMatch::ACKNOWLEDGEMENT => SmallTalkLexicon::ACK_PHRASES,
            SmallTalkMatch::FAREWELL => SmallTalkLexicon::FAREWELL_PHRASES,
        ];

        $best = null;

        foreach ($sets as $category => $phrases) {
            foreach ($phrases as $phrase) {
                $length = count($phrase);

                if (array_slice($words, $offset, $length) !== $phrase) {
                    continue;
                }

                // Longest phrase wins, so "thanks a lot" is not consumed as
                // the shorter "thanks" with stray words after it.
                if ($best === null || $length > $best[0]) {
                    $best = [$length, $category];
                }
            }
        }

        return $best;
    }

    /**
     * A typo'd greeting, deliberately hemmed in.
     *
     * levenshtein() only — similar_text() is superlinear and a percentage
     * threshold is far harder to reason about than an integer edit distance.
     *
     * The strlen >= 3 floor is the load-bearing guard. Without it every one of
     * these is distance 1 from a greeting and a real question would be eaten:
     * "no" -> "yo", "of" -> "ok", "is" -> "hi".
     *
     * The ASCII check guarantees levenshtein()'s byte distance equals the
     * character distance — it is not multibyte-aware, so "kamustá" must not be
     * measured in bytes.
     */
    private function fuzzyGreeting(string $word, int $totalTokens): bool
    {
        if (! (bool) config('chat.smalltalk.fuzzy_enabled', true)) {
            return false;
        }

        // First token of a short message only. A typo buried mid-sentence is
        // not a greeting.
        if ($totalTokens > 3) {
            return false;
        }

        if (! preg_match("/^[a-z']+$/", $word)) {
            return false;
        }

        $length = strlen($word);
        $minLength = (int) config('chat.smalltalk.fuzzy_min_length', 3);

        if ($length < $minLength || $length > 12) {
            return false;
        }

        $tolerance = $length <= 5 ? 1 : 2;

        foreach (SmallTalkLexicon::GREETING_UNIGRAMS as $candidate) {
            // An exact match is handled by the caller; only near misses here.
            if ($candidate === $word) {
                return false;
            }

            if (levenshtein($word, $candidate) <= $tolerance) {
                return true;
            }
        }

        return false;
    }

    /**
     * The text after a leading small-talk span, or null when the boundary
     * does not look like a greeting handoff.
     *
     * This is guard G1b, and it is what keeps "hi-tech aquaculture" whole. In
     * that phrase the normalized tokens are ["hi", "tech", "aquaculture"], so
     * "hi" matches the lexicon and a naive strip would leave "tech
     * aquaculture" — turning a real question into a different one. A span is
     * only stripped when it is followed by punctuation, or by whitespace and
     * then a recognised question opener.
     *
     * @param  array<int, array{text: string, start: int, end: int}>  $tokens
     */
    private function residualAfter(string $original, array $tokens, int $spanLength): ?string
    {
        $normalized = $this->normalize($original);
        $spanEnd = $tokens[$spanLength - 1]['end'];
        $after = substr($normalized, $spanEnd);

        // Punctuation right after the greeting is an unambiguous boundary.
        $hasPunctuationBoundary = (bool) preg_match('/^\s*[,.!?;:]/', $after);

        if (! $hasPunctuationBoundary) {
            $nextWord = $tokens[$spanLength]['text'] ?? null;

            if ($nextWord === null || ! in_array($nextWord, SmallTalkLexicon::QUESTION_OPENERS, true)) {
                return null;
            }
        }

        // Map back onto the ORIGINAL string so the user's own casing and
        // wording survive into retrieval and the prompt.
        $residual = $this->residualFromOriginal($original, $spanLength, $tokens);

        return $residual === '' ? null : $residual;
    }

    /**
     * @param  array<int, array{text: string, start: int, end: int}>  $tokens
     */
    private function residualFromOriginal(string $original, int $spanLength, array $tokens): string
    {
        // Normalization only lowercases, strips emoji and collapses runs and
        // whitespace — it never reorders words. So locating the first residual
        // word in the original is enough to find the cut point, and it avoids
        // depending on normalized offsets lining up with original ones (letter
        // run collapsing means they do not).
        $firstResidualWord = $tokens[$spanLength]['text'] ?? '';

        if ($firstResidualWord === '') {
            return '';
        }

        $position = mb_stripos($original, $firstResidualWord);

        if ($position === false) {
            // Collapsed letter runs can make the normalized token absent from
            // the original ("helloooo" -> "hello"). Fall back to cutting at
            // the first boundary punctuation, then the first whitespace.
            if (preg_match('/[,.!?;:]\s*/u', $original, $m, PREG_OFFSET_CAPTURE)) {
                return trim(substr($original, $m[0][1] + strlen($m[0][0])));
            }

            return '';
        }

        return trim(mb_substr($original, $position));
    }
}
