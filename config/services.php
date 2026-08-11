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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.1-flash-lite'),
        'fallback_models' => [
            'gemini-3.6-flash',
            'gemini-3.1-flash-lite',
        ],
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'fallback_models' => [
            'qwen-qwq-32b',
            'gemma2-9b-it',
        ],
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'google/gemma-4-26b-a4b-it:free'),
        'fallback_models' => [
            'nvidia/nemotron-3-super-120b-a12b:free',
            'nvidia/nemotron-3-ultra-550b-a55b:free',
            'google/gemma-4-31b-it:free',
            'nvidia/nemotron-nano-9b-v2:free',
            'openai/gpt-oss-20b:free',
            'nvidia/nemotron-nano-12b-v2-vl:free',
            'inclusionai/ling-3.0-flash:free',
        ],
        'embedding_model' => env('OPENROUTER_EMBEDDING_MODEL', 'nvidia/nemotron-3-embed-1b:free'),
        'embedding_fallback_models' => [
            'nvidia/llama-nemotron-embed-vl-1b-v2:free',
            'openai/text-embedding-3-small',
        ],
        'timeout' => 15,
        'max_retries' => 3,
        'rate_limit_per_minute' => 60,
    ],

];
