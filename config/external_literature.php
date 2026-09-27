<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External open-access literature
    |--------------------------------------------------------------------------
    |
    | Lets the chatbot mention published research that is NOT in this
    | repository — titles and abstracts from a public open-access index — so a
    | question the local corpus cannot answer gets something more useful than
    | "I couldn't find enough information".
    |
    | METADATA ONLY. Titles, authors, year, DOI and abstract. No full text is
    | fetched, nothing is embedded, and nothing is stored as a corpus: see the
    | header of App\Services\External\ExternalLiteratureService for why a
    | harvest table was rejected.
    |
    | DEFAULT OFF. A student records system making outbound calls on a user's
    | question should be a deployment decision, not a default. With 'driver'
    | set to 'null' the app behaves exactly as it did before this existed —
    | App\Services\External\NullLiteratureProvider returns nothing, and the
    | classifier stands down.
    |
    */

    'enabled' => (bool) env('EXTERNAL_LITERATURE_ENABLED', false),

    'driver' => env('EXTERNAL_LITERATURE_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | When it may fire
    |--------------------------------------------------------------------------
    |
    | 'on_no_internal_evidence' lets a research question that found nothing
    | locally fall through to external suggestions. It is NEVER consulted when
    | internal evidence exists: mixing outside literature into an answer
    | grounded in this repository's own studies is exactly the confusion the
    | citation namespace and the separate UI list exist to prevent.
    |
    */

    'on_no_internal_evidence' => (bool) env('EXTERNAL_LITERATURE_ON_NO_EVIDENCE', true),

    /*
    |--------------------------------------------------------------------------
    | Budget
    |--------------------------------------------------------------------------
    |
    | External lookups cost no OpenAI tokens — results are rendered as canned
    | text plus citation cards rather than being passed through the model. They
    | are not free of goodwill, though: OpenAlex serves this for nothing and
    | deserves not to be hammered.
    |
    */

    'max_works' => (int) env('EXTERNAL_LITERATURE_MAX_WORKS', 5),

    'abstract_chars' => (int) env('EXTERNAL_LITERATURE_ABSTRACT_CHARS', 240),

    'per_user_per_day' => (int) env('EXTERNAL_LITERATURE_PER_USER_PER_DAY', 20),

    'cache_ttl_days' => (int) env('EXTERNAL_LITERATURE_CACHE_TTL_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    |
    | No retries, deliberately. This runs inside a serverless function on a
    | request the user is watching, and a retry doubles the worst-case latency
    | of a SECONDARY source. A failed lookup degrades to the ordinary
    | no-evidence answer instead.
    |
    | 'mailto' is OpenAlex's documented politeness convention — it puts the
    | caller in their faster "polite pool" and gives them someone to contact
    | before blocking an abusive client.
    |
    */

    'connect_timeout' => (int) env('EXTERNAL_LITERATURE_CONNECT_TIMEOUT', 3),

    'timeout' => (int) env('EXTERNAL_LITERATURE_TIMEOUT', 6),

    'mailto' => env('EXTERNAL_LITERATURE_MAILTO'),

    /*
    |--------------------------------------------------------------------------
    | Circuit breaker
    |--------------------------------------------------------------------------
    |
    | After this many failures inside the window, stop calling out for
    | 'open_seconds' and behave as if the feature were disabled. Without it, a
    | provider outage adds its full timeout to every single chat turn that
    | reaches the external path.
    |
    */

    'circuit' => [
        'failure_threshold' => (int) env('EXTERNAL_LITERATURE_CIRCUIT_FAILURES', 3),
        'window_seconds' => (int) env('EXTERNAL_LITERATURE_CIRCUIT_WINDOW', 300),
        'open_seconds' => (int) env('EXTERNAL_LITERATURE_CIRCUIT_OPEN', 300),
    ],

];
