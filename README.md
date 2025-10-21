# Automata Kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/automata-kit/automata-kit.svg?style=flat-square)](https://packagist.org/packages/automata-kit/automata-kit)
[![Total Downloads](https://img.shields.io/packagist/dt/automata-kit/automata-kit.svg?style=flat-square)](https://packagist.org/packages/automata-kit/automata-kit)
[![License](https://img.shields.io/packagist/l/automata-kit/automata-kit.svg?style=flat-square)](https://packagist.org/packages/automata-kit/automata-kit)

A comprehensive Laravel package for seamless automation integrations with tools like **n8n**, **Zapier**, **Make**, **Slack**, **Telegram**, **WhatsApp**, **Google Sheets**, **Airtable**, **Discord**, **HubSpot**, **Google Drive**, and **OpenAI**.

## 🚀 Features

- **Multi-Service Integration**: Built-in drivers for 12+ popular automation and communication platforms
- **Unified Interface**: Single API to interact with all services using Laravel's familiar syntax  
- **Webhook Management**: Auto-generate secure webhook endpoints with signature verification
- **Event-Driven**: Automatic Laravel event dispatching for all webhook interactions
- **Queue Support**: Built-in async processing with Laravel's queue system
- **Extensible**: Easy-to-create custom drivers for any service
- **Filament Dashboard**: Optional admin interface for monitoring and management
- **Production Ready**: Comprehensive logging, error handling, and security features

## 📦 Installation

Install the package via Composer:

```bash
composer require automata-kit/automata-kit
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=automation-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=automation-migrations
php artisan migrate
```

## ⚙️ Configuration

Add your service credentials to your `.env` file:

```env
# Slack
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
SLACK_BOT_TOKEN=xoxb-your-bot-token
SLACK_SIGNING_SECRET=your-signing-secret

# n8n
N8N_BASE_URL=https://your-n8n-instance.com
N8N_WEBHOOK_URL=https://your-n8n-instance.com/webhook/your-webhook-id
N8N_API_KEY=your-api-key

# Telegram
TELEGRAM_BOT_TOKEN=your-bot-token
TELEGRAM_DEFAULT_CHAT_ID=your-chat-id

# WhatsApp Business API
WHATSAPP_ACCESS_TOKEN=your-access-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-number-id

# OpenAI
OPENAI_API_KEY=your-api-key

# HubSpot
HUBSPOT_ACCESS_TOKEN=your-access-token

# Airtable
AIRTABLE_API_KEY=your-api-key
AIRTABLE_BASE_ID=your-base-id

# Discord
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your/webhook/url

# Zapier & Make
ZAPIER_WEBHOOK_URL=https://hooks.zapier.com/hooks/catch/your/webhook
MAKE_WEBHOOK_URL=https://hook.integromat.com/your/webhook/url
```

## 🎯 Quick Start

### Sending Data to Services

```php
use AutomataKit\LaravelAutomationConnect\Facades\Automation;

// Send a Slack message
Automation::to('slack')->send([
    'text' => 'New order received! 🎉',
    'channel' => '#notifications'
]);

// Trigger an n8n workflow
Automation::to('n8n')->send([
    'customer_id' => 123,
    'order_total' => 150.00,
    'event' => 'order_created'
]);

// Send a Telegram notification
Automation::to('telegram')->send([
    'chat_id' => '-123456789',
    'text' => 'Your order has been shipped! 📦'
]);

// Create a HubSpot contact
Automation::to('hubspot')->send([
    'email' => 'customer@example.com',
    'firstname' => 'John',
    'lastname' => 'Doe'
]);
```

### Receiving Webhooks

The package automatically creates webhook endpoints at `/webhooks/{service}/{event?}`. Configure your external services to send webhooks to:

```
https://yourapp.com/webhooks/slack
https://yourapp.com/webhooks/n8n/order-completed
https://yourapp.com/webhooks/telegram
```

### Handling Webhook Events

Create event listeners to process incoming webhooks:

```php
use AutomataKit\LaravelAutomationConnect\Events\WebhookReceived;

class SlackWebhookListener
{
    public function handle(WebhookReceived $event): void
    {
        if ($event->service === 'slack' && $event->event === 'app_mention') {
            // Bot was mentioned in Slack
            $text = $event->payload['event']['text'];
            $channel = $event->payload['event']['channel'];
            
            // Respond automatically
            Automation::to('slack')->send([
                'channel' => $channel,
                'text' => "I heard you mention me! 👋"
            ]);
        }
    }
}
```

Register in your `EventServiceProvider`:

```php
protected $listen = [
    WebhookReceived::class => [
        SlackWebhookListener::class,
    ],
];
```

## 🔧 Available Drivers

### Communication Platforms
- **Slack**: Send messages, handle events, slash commands
- **Telegram**: Send messages, handle updates, inline keyboards
- **WhatsApp**: Send messages via WhatsApp Business API
- **Discord**: Send messages via webhooks

### Automation Platforms
- **n8n**: Trigger workflows, handle webhook responses
- **Zapier**: Send data to Zapier webhooks
- **Make** (Integromat): Trigger Make scenarios

### Business Tools
- **HubSpot**: Create/update contacts, deals, companies
- **Airtable**: Create/update records in bases
- **Google Sheets**: Read/write spreadsheet data (requires OAuth)
- **Google Drive**: Upload/manage files (requires OAuth)

### AI Services
- **OpenAI**: Generate completions, embeddings, chat responses

## 🎨 Advanced Usage

### Custom Drivers

Create custom drivers for any service:

```php
use AutomataKit\LaravelAutomationConnect\Drivers\BaseDriver;
use Illuminate\Http\Request;

class CustomServiceDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'custom_service';
    }

    public function send(array $data, array $options = []): mixed
    {
        $apiKey = $this->getConfigValue('api_key');
        
        return $this->makeRequest('POST', 'https://api.example.com/webhook', [
            'headers' => ['Authorization' => "Bearer {$apiKey}"],
            'json' => $data,
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        return ['status' => 'received', 'data' => $request->all()];
    }
}
```

Register your custom driver:

```php
// In AppServiceProvider
$this->app->bind('automation.driver.custom_service', CustomServiceDriver::class);
```

### Async Processing with Queues

Enable queue processing for webhook handling:

```php
use AutomataKit\LaravelAutomationConnect\Events\WebhookReceived;
use Illuminate\Contracts\Queue\ShouldQueue;

class AsyncWebhookListener implements ShouldQueue
{
    public function handle(WebhookReceived $event): void
    {
        // This will be processed asynchronously
        ProcessWebhookJob::dispatch($event->service, $event->payload);
    }
}
```

### Conditional Sending

Send to different services based on conditions:

```php
$drivers = ['slack', 'discord', 'telegram'];

foreach ($drivers as $driver) {
    if (Automation::hasDriver($driver)) {
        Automation::to($driver)->send([
            'message' => "Alert: System maintenance in 10 minutes"
        ]);
    }
}
```

## 📊 Filament Dashboard

Enable the optional Filament dashboard to monitor webhook logs:

```env
AUTOMATION_DASHBOARD_ENABLED=true
```

Install Filament (if not already installed):

```bash
composer require filament/filament
```

The dashboard provides:
- Real-time webhook logs and status
- Success/failure analytics  
- Retry failed webhooks
- Filter and search capabilities
- Performance metrics

## 🔒 Security

### Webhook Verification

Enable webhook signature verification:

```env
AUTOMATION_VERIFY_WEBHOOKS=true
SLACK_SIGNING_SECRET=your-slack-signing-secret
```

### IP Restrictions

Restrict webhook access to specific IPs:

```env
AUTOMATION_ALLOWED_IPS=192.168.1.1,10.0.0.0/8
```

### HTTPS Enforcement

Require HTTPS for webhook endpoints:

```env
AUTOMATION_REQUIRE_HTTPS=true
```

## 📝 Real-World Examples

### E-commerce Order Processing

```php
// When an order is created
class OrderCreated
{
    public function handle($order): void
    {
        // Notify team on Slack
        Automation::to('slack')->send([
            'text' => "🛒 New order #{$order->id} - ${$order->total}",
            'blocks' => [
                [
                    'type' => 'section',
                    'fields' => [
                        ['type' => 'mrkdwn', 'text' => "*Customer:* {$order->customer->name}"],
                        ['type' => 'mrkdwn', 'text' => "*Total:* ${$order->total}"]
                    ]
                ]
            ]
        ]);

        // Add to CRM
        Automation::to('hubspot')->send([
            'email' => $order->customer->email,
            'total_revenue' => $order->total,
            'last_purchase_date' => now()->toDateString(),
        ]);

        // Update spreadsheet
        Automation::to('airtable')->send([
            'Order ID' => $order->id,
            'Customer' => $order->customer->name,
            'Total' => $order->total,
            'Date' => $order->created_at->toDateString(),
        ], ['table' => 'Orders']);

        // Trigger fulfillment workflow
        Automation::to('n8n')->send([
            'order_id' => $order->id,
            'items' => $order->items->toArray(),
            'shipping_address' => $order->shipping_address,
        ]);
    }
}
```

### Customer Support Integration

```php
class SupportTicketCreated
{
    public function handle($ticket): void
    {
        // Notify support team
        Automation::to('slack')->send([
            'channel' => '#support',
            'text' => "🎫 New support ticket #{$ticket->id}",
            'attachments' => [
                [
                    'color' => $ticket->priority === 'high' ? 'danger' : 'warning',
                    'fields' => [
                        ['title' => 'Customer', 'value' => $ticket->customer->name],
                        ['title' => 'Subject', 'value' => $ticket->subject],
                        ['title' => 'Priority', 'value' => strtoupper($ticket->priority)],
                    ]
                ]
            ]
        ]);

        // Generate AI response suggestion
        $aiResponse = Automation::to('openai')->send([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful customer support assistant. Provide a professional response.'
                ],
                [
                    'role' => 'user', 
                    'content' => $ticket->description
                ]
            ]
        ]);

        // Log the suggestion for support agent
        $ticket->notes()->create([
            'content' => "AI Suggestion: " . $aiResponse['choices'][0]['message']['content'],
            'type' => 'ai_suggestion'
        ]);
    }
}
```

### Marketing Automation

```php
class NewsletterSignup
{
    public function handle($subscriber): void
    {
        // Add to CRM
        Automation::to('hubspot')->send([
            'email' => $subscriber->email,
            'firstname' => $subscriber->name,
            'hs_lead_status' => 'NEW',
            'lifecyclestage' => 'subscriber',
        ]);

        // Send welcome message
        Automation::to('telegram')->send([
            'chat_id' => $subscriber->telegram_chat_id,
            'text' => "🎉 Welcome to our newsletter, {$subscriber->name}! Thanks for subscribing.",
        ]);

        // Update analytics
        Automation::to('google_sheets')->send([
            'Date' => now()->toDateString(),
            'Email' => $subscriber->email,
            'Source' => $subscriber->source,
            'Campaign' => $subscriber->campaign,
        ], ['spreadsheet' => 'newsletter-analytics']);
    }
}
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

## 📚 Documentation

Complete documentation and examples are included in this package.

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 🔒 Security

If you discover any security vulnerabilities, please use GitHub issues or contact the package author.

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🚀 Roadmap

- [ ] Google Calendar integration
- [ ] Microsoft Teams support  
- [ ] Salesforce connector
- [ ] Notion API integration
- [ ] Webhook rate limiting & throttling
- [ ] GraphQL webhook support
- [ ] Multi-tenant support

---

Developed by Mohammed Mustafa for the Laravel community.