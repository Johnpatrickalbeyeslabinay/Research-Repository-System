<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Used by App\Services\Ai\MetadataAnalysisService (keyword/SDG
    // suggestions from title+abstract) and App\Services\Research\EmbeddingService
    // (document chunk embeddings for RAG). Models are configurable here
    // rather than hardcoded so they can change without touching business logic.
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'metadata_model' => env('OPENAI_METADATA_MODEL', 'gpt-4o-mini'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'metadata_max_input_tokens' => env('OPENAI_METADATA_MAX_INPUT_TOKENS', 2000),
        'metadata_max_output_tokens' => env('OPENAI_METADATA_MAX_OUTPUT_TOKENS', 300),
        // AI abstract generation (App\Services\Ai\AbstractGenerationService)
        // — only for papers with no detectable abstract, on explicit click.
        'abstract_model' => env('OPENAI_ABSTRACT_MODEL', env('OPENAI_METADATA_MODEL', 'gpt-4o-mini')),
        'abstract_max_input_tokens' => env('OPENAI_ABSTRACT_MAX_INPUT_TOKENS', 3000),
        'abstract_max_output_tokens' => env('OPENAI_ABSTRACT_MAX_OUTPUT_TOKENS', 450),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),
    'psql_path' => env('PSQL_PATH', 'psql'),

];
