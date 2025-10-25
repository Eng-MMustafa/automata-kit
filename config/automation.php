<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Automation Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default automation driver that will be used
    | when no specific driver is specified. You may change this default
    | as needed, but this is a perfect start for any application.
    |
    */
    'default' => env('AUTOMATION_DRIVER', 'slack'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how incoming webhooks should be handled.
    |
    */
    'webhook_prefix' => env('AUTOMATION_WEBHOOK_PREFIX', 'webhooks'),
    'webhook_middleware' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for automation activities.
    |
    */
    'logging' => [
        'enabled' => env('AUTOMATION_LOGGING_ENABLED', true),
        'channel' => env('AUTOMATION_LOG_CHANNEL', 'stack'),
        'level' => env('AUTOMATION_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for async automation processing.
    |
    */
    'queue' => [
        'enabled' => env('AUTOMATION_QUEUE_ENABLED', true),
        'connection' => env('AUTOMATION_QUEUE_CONNECTION', 'default'),
        'queue' => env('AUTOMATION_QUEUE_NAME', 'automations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automation Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the automation drivers for your application.
    | Each driver supports different authentication methods and features.
    |
    */
    'drivers' => [
        'slack' => [
            'driver' => 'slack',
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'bot_token' => env('SLACK_BOT_TOKEN'),
            'signing_secret' => env('SLACK_SIGNING_SECRET'),
            'default_channel' => env('SLACK_DEFAULT_CHANNEL', '#general'),
        ],

        'n8n' => [
            'driver' => 'n8n',
            'base_url' => env('N8N_BASE_URL'),
            'webhook_url' => env('N8N_WEBHOOK_URL'),
            'api_key' => env('N8N_API_KEY'),
            'basic_auth' => [
                'username' => env('N8N_USERNAME'),
                'password' => env('N8N_PASSWORD'),
            ],
        ],

        'zapier' => [
            'driver' => 'zapier',
            'webhook_url' => env('ZAPIER_WEBHOOK_URL'),
        ],

        'make' => [
            'driver' => 'make',
            'webhook_url' => env('MAKE_WEBHOOK_URL'),
        ],

        'telegram' => [
            'driver' => 'telegram',
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),
            'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        ],

        'whatsapp' => [
            'driver' => 'whatsapp',
            'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        ],

        'google_sheets' => [
            'driver' => 'google_sheets',
            'service_account_key' => env('GOOGLE_SERVICE_ACCOUNT_KEY'),
            'default_spreadsheet_id' => env('GOOGLE_SHEETS_DEFAULT_SPREADSHEET_ID'),
        ],

        'airtable' => [
            'driver' => 'airtable',
            'api_key' => env('AIRTABLE_API_KEY'),
            'base_id' => env('AIRTABLE_BASE_ID'),
            'default_table' => env('AIRTABLE_DEFAULT_TABLE'),
        ],

        'discord' => [
            'driver' => 'discord',
            'webhook_url' => env('DISCORD_WEBHOOK_URL'),
            'bot_token' => env('DISCORD_BOT_TOKEN'),
        ],

        'hubspot' => [
            'driver' => 'hubspot',
            'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
            'api_key' => env('HUBSPOT_API_KEY'),
        ],

        'google_drive' => [
            'driver' => 'google_drive',
            'service_account_key' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_KEY'),
            'default_folder_id' => env('GOOGLE_DRIVE_DEFAULT_FOLDER_ID'),
        ],

        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different drivers to prevent API abuse.
    |
    */
    'rate_limiting' => [
        'enabled' => env('AUTOMATION_RATE_LIMITING_ENABLED', true),
        'default_limit' => env('AUTOMATION_RATE_LIMIT', 60), // per minute
        'driver_limits' => [
            'slack' => 100,
            'telegram' => 30,
            'whatsapp' => 10,
            'openai' => 20,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for webhook verification and data handling.
    |
    */
    'security' => [
        'verify_webhooks' => env('AUTOMATION_VERIFY_WEBHOOKS', true),
        'allowed_ips' => env('AUTOMATION_ALLOWED_IPS', null), // comma-separated IPs
        'require_https' => env('AUTOMATION_REQUIRE_HTTPS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how webhook events are handled and dispatched.
    |
    */
    'events' => [
        'dispatch_events' => env('AUTOMATION_DISPATCH_EVENTS', true),
        'event_prefix' => env('AUTOMATION_EVENT_PREFIX', 'automation'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Dashboard
    |--------------------------------------------------------------------------
    |
    | Configure the optional Filament admin dashboard.
    |
    */
    'dashboard' => [
        'enabled' => env('AUTOMATION_DASHBOARD_ENABLED', false),
        'middleware' => ['web', 'auth'],
        'prefix' => 'automation',
    ],
];
