<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Per-model token prices
    |--------------------------------------------------------------------------
    |
    | USD per ONE MILLION tokens, as published by the provider. Used by
    | App\Support\AiCost to turn the raw token counts in ai_usage_logs into
    | money, which is the only unit anyone actually makes a decision in.
    |
    | Cost is deliberately COMPUTED at report time, never stored as a column
    | on ai_usage_logs. A stored cost silently becomes wrong history the
    | moment a price changes, and there is no way to tell a stale row from a
    | fresh one. Computing it means a price correction here re-prices every
    | past row, which is the behaviour you want from a cost report.
    |
    | 'cached_input' is the discounted rate for prompt-prefix tokens the
    | provider served from its automatic cache. NOTE: that cache only
    | engages on prompts of 1024 tokens or more, and this chatbot's shared
    | system prefix measures ~950-1020 tokens on its own — so short turns
    | (small talk, catalog, abstract-tier) often qualify for no discount at
    | all. Do not build a saving estimate on caching; see the prompt-caching
    | note in App\Services\Chat\PromptBuilder.
    |
    | Verify against the provider's current pricing page before trusting a
    | report; these are the rates as of the last edit, not a live feed.
    |
    */

    'models' => [

        'gpt-4o-mini' => [
            'input' => 0.15,
            'cached_input' => 0.075,
            // Output is FOUR TIMES the input rate. That asymmetry is why
            // per-tier max_output_tokens ceilings matter more to the bill
            // than trimming the prompt does: a 700-token answer costs the
            // same as a 2,800-token prompt.
            'output' => 0.60,
        ],

        'gpt-4o' => [
            'input' => 2.50,
            'cached_input' => 1.25,
            'output' => 10.00,
        ],

        'text-embedding-3-small' => [
            'input' => 0.02,
            'cached_input' => 0.02,
            'output' => 0.0,
        ],

        'text-embedding-3-large' => [
            'input' => 0.13,
            'cached_input' => 0.13,
            'output' => 0.0,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Unknown models
    |--------------------------------------------------------------------------
    |
    | An operation logged with a model that has no entry above (a model swap
    | that forgot this file, or the literal 'none' that zero-token paths and
    | external metadata lookups record) prices at zero rather than throwing.
    | A cost report must never be the thing that breaks — but it does warn,
    | so a missing price shows up as a visible gap rather than as quietly
    | understated spend.
    |
    */

    'warn_on_unknown_model' => true,

    /*
    |--------------------------------------------------------------------------
    | Display currency
    |--------------------------------------------------------------------------
    |
    | Reports are produced in USD (what the provider bills) and optionally
    | converted for local budgeting. This is a static rate for rough
    | reporting, not an FX feed — set it to whatever the finance
    | conversation actually uses.
    |
    */

    'php_per_usd' => (float) env('AI_PHP_PER_USD', 58.0),

];
