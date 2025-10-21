<?php

// Example: Basic Slack integration
use AutomataKit\LaravelAutomationConnect\Facades\Automation;

// Send a simple message to Slack
Automation::to('slack')->send([
    'text' => 'New order received!',
    'channel' => '#notifications'
]);

// Send a rich message with attachments
Automation::to('slack')->send([
    'text' => 'Order #12345 created',
    'blocks' => [
        [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => '*Order Details*\n• Customer: John Doe\n• Total: $150.00'
            ]
        ]
    ]
]);

// Example: n8n workflow trigger
Automation::to('n8n')->send([
    'customer_id' => 123,
    'order_total' => 150.00,
    'items' => [
        ['name' => 'Product A', 'qty' => 2],
        ['name' => 'Product B', 'qty' => 1]
    ]
]);

// Example: Telegram notification
Automation::to('telegram')->send([
    'chat_id' => '-123456789',
    'text' => '🎉 New customer registered: <b>John Doe</b>',
    'parse_mode' => 'HTML'
]);

// Example: WhatsApp message
Automation::to('whatsapp')->send([
    'to' => '+1234567890',
    'message' => 'Thank you for your order! Your tracking number is: ABC123'
]);

// Example: OpenAI integration
$response = Automation::to('openai')->send([
    'model' => 'gpt-4',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful customer service assistant.'
        ],
        [
            'role' => 'user',
            'content' => 'How do I track my order?'
        ]
    ],
    'max_tokens' => 150
]);

// Example: HubSpot contact creation
Automation::to('hubspot')->send([
    'email' => 'john@example.com',
    'firstname' => 'John',
    'lastname' => 'Doe',
    'phone' => '+1234567890',
    'company' => 'Example Corp'
]);

// Example: Airtable record creation
Automation::to('airtable')->send([
    'Name' => 'John Doe',
    'Email' => 'john@example.com',
    'Status' => 'New Lead',
    'Source' => 'Website Form'
], ['table' => 'Leads']);

// Example: Discord notification
Automation::to('discord')->send([
    'content' => '🚀 New deployment completed successfully!',
    'username' => 'Deploy Bot'
]);