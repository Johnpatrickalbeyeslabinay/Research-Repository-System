<?php

namespace App\Services\Chat\SmallTalk;

/**
 * The vocabulary SmallTalkDetector matches against — data only, no logic,
 * so a reviewer can check "is this word really small talk?" without reading
 * any matching code.
 *
 * Why a lexicon replaced a regex: the old GreetingDetector anchored whole
 * patterns (/^(hi+|hello+|...)$/), which meant "hi there", "hello po" and
 * "good day sir" all missed the free path and instead paid for a query
 * embedding plus a ~2,500-token answer call, only to produce a stiff reply
 * about the evidence not covering a greeting. Matching a token SET instead
 * keeps the anchor's tight false-positive behaviour while accepting the
 * things people actually type.
 *
 * Users are at CSU Aparri in Cagayan, so Tagalog and Ilocano greetings are
 * ordinary input rather than an edge case. Replies stay in English (the
 * whole UI is) with only the greeting word recognised — half-translated copy
 * reads worse than none.
 */
final class SmallTalkLexicon
{
    /**
     * Politeness particles, dropped before matching. Only ever removed
     * inside a candidate small-talk span, never from a real question —
     * "sir" in "who is the adviser sir" is stripped, but that message has
     * non-lexicon tokens left over and so is not small talk anyway.
     */
    public const PARTICLES = [
        'po', 'ho', 'na', 'nga', 'lang', 'sana', 'ba', 'ka', 'kayo',
        'sir', 'maam', "ma'am", 'madam', 'doc', 'idol',
        'there', 'everyone', 'all', 'guys', 'bro', 'ate', 'kuya',
        'please', 'pls', 'thanks',
    ];

    /** Single-token greetings. */
    public const GREETING_UNIGRAMS = [
        'hi', 'hello', 'hey', 'helo', 'hallo', 'hiya', 'howdy', 'aloha',
        'yo', 'sup', 'wassup', 'whatsup', 'greetings', 'hai',
        // Tagalog / Ilocano / Philippine-English openers.
        'kamusta', 'kumusta', 'kmusta', 'musta',
    ];

    /**
     * Multi-token greetings, matched as CONTIGUOUS token sequences. That
     * contiguity matters: "how are the fish populations doing?" must not
     * match "how are you", and it cannot, because "how are the" is not this
     * sequence.
     */
    public const GREETING_PHRASES = [
        ['good', 'morning'],
        ['good', 'afternoon'],
        ['good', 'evening'],
        ['good', 'day'],
        ['good', 'noon'],
        ['magandang', 'umaga'],
        ['magandang', 'tanghali'],
        ['magandang', 'hapon'],
        ['magandang', 'gabi'],
        ['magandang', 'araw'],
        ['naimbag', 'nga', 'bigat'],
        ['naimbag', 'a', 'bigat'],
        ['naimbag', 'nga', 'malem'],
        ['naimbag', 'nga', 'rabii'],
        ['what', 'is', 'up'],
        ['whats', 'up'],
        ['how', 'are', 'you'],
        ['how', 'are', 'u'],
        ['kumusta', 'ka'],
        ['kamusta', 'ka'],
        ['nice', 'to', 'meet', 'you'],
    ];

    /** Single-token acknowledgements. */
    public const ACK_UNIGRAMS = [
        'thanks', 'thank', 'thx', 'tnx', 'ty', 'tysm',
        'salamat', 'ok', 'okay', 'okey', 'oks', 'sige',
        'gotcha', 'noted', 'alright', 'aright', 'yes', 'yep', 'yup',
    ];

    /**
     * Acknowledgements that are ONLY acknowledgements when they are the
     * entire message. Each of these is a perfectly ordinary adjective in a
     * research question, so accepting them inside a longer span would
     * misread "great findings on tilapia" or "cool water species" as chatter.
     */
    public const ACK_SOLO_ONLY = [
        'cool', 'nice', 'great', 'awesome', 'perfect', 'galing', 'ayos', 'wow',
    ];

    /** Multi-token acknowledgements, matched contiguously. */
    public const ACK_PHRASES = [
        ['thank', 'you'],
        ['thanks', 'a', 'lot'],
        ['many', 'thanks'],
        ['maraming', 'salamat'],
        ['salamat', 'po'],
        ['got', 'it'],
        ['sounds', 'good'],
        ['appreciate', 'it'],
        ['no', 'problem'],
        ['never', 'mind'],
        ['will', 'do'],
    ];

    /** Farewells — treated as acknowledgements for reply purposes. */
    public const FAREWELL_UNIGRAMS = [
        'bye', 'goodbye', 'byebye', 'paalam', 'ingat',
    ];

    /** @var array<int, string[]> */
    public const FAREWELL_PHRASES = [
        ['see', 'you'],
        ['see', 'ya'],
        ['good', 'night'],
        ['that', 'is', 'all'],
        ['thats', 'all'],
        ['i', 'am', 'done'],
        ['im', 'done'],
    ];

    /**
     * Question openers. Used by the boundary rule: a greeting span is only
     * stripped when what follows it is punctuation, or whitespace plus one
     * of these. That is what keeps "hi-tech aquaculture" intact — "tech" is
     * not an opener, so nothing is stripped and the full phrase routes as a
     * real question.
     */
    public const QUESTION_OPENERS = [
        'what', 'which', 'how', 'who', 'whom', 'whose', 'when', 'where', 'why',
        'can', 'could', 'do', 'does', 'did', 'is', 'are', 'was', 'were',
        'tell', 'show', 'list', 'find', 'give', 'recommend', 'suggest',
        'summarize', 'summarise', 'compare', 'explain', 'describe',
        'i', 'im', 'ano', 'sino', 'saan', 'paano',
    ];
}
