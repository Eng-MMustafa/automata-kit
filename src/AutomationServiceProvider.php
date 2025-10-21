<?php

namespace AutomataKit\LaravelAutomationConnect;

use AutomataKit\LaravelAutomationConnect\Http\Controllers\WebhookController;
use AutomataKit\LaravelAutomationConnect\Services\AutomationManager;
use AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract;
use AutomataKit\LaravelAutomationConnect\Drivers\SlackDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\N8nDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\ZapierDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\TelegramDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\WhatsAppDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\GoogleSheetsDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\AirtableDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\DiscordDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\HubSpotDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\GoogleDriveDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\OpenAIDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\MakeDriver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AutomationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/automation.php',
            'automation'
        );

        // Bind the automation manager
        $this->app->singleton(AutomationManager::class, function ($app) {
            return new AutomationManager($app);
        });

        // Bind the automation alias
        $this->app->alias(AutomationManager::class, 'automation');

        // Register default drivers
        $this->registerDefaultDrivers();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/automation.php' => config_path('automation.php'),
        ], 'automation-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'automation-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register routes
        $this->registerRoutes();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add console commands here
            ]);
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('automation.webhook_prefix', 'webhooks'),
            'middleware' => config('automation.webhook_middleware', []),
        ], function () {
            Route::post('/{service}/{event?}', [WebhookController::class, 'handle'])
                ->name('automation.webhook');
        });
    }

    /**
     * Register default automation drivers.
     */
    protected function registerDefaultDrivers(): void
    {
        $drivers = [
            'slack' => SlackDriver::class,
            'n8n' => N8nDriver::class,
            'zapier' => ZapierDriver::class,
            'telegram' => TelegramDriver::class,
            'whatsapp' => WhatsAppDriver::class,
            'google_sheets' => GoogleSheetsDriver::class,
            'airtable' => AirtableDriver::class,
            'discord' => DiscordDriver::class,
            'hubspot' => HubSpotDriver::class,
            'google_drive' => GoogleDriveDriver::class,
            'openai' => OpenAIDriver::class,
            'make' => MakeDriver::class,
        ];

        foreach ($drivers as $name => $driverClass) {
            $this->app->bind("automation.driver.{$name}", $driverClass);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AutomationManager::class,
            'automation',
        ];
    }
}