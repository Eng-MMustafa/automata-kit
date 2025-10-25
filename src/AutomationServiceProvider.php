<?php

namespace AutomataKit\LaravelAutomationConnect;

use AutomataKit\LaravelAutomationConnect\Drivers\AirtableDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\DiscordDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\GoogleDriveDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\GoogleSheetsDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\HubSpotDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\MakeDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\N8nDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\OpenAIDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\SlackDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\TelegramDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\WhatsAppDriver;
use AutomataKit\LaravelAutomationConnect\Drivers\ZapierDriver;
use AutomataKit\LaravelAutomationConnect\Services\AutomationManager;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AutomationServiceProvider extends PackageServiceProvider
{
    /**
     * Configuring the package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('automation')
            ->hasInstallCommand(
                fn (InstallCommand $command): \Spatie\LaravelPackageTools\Commands\InstallCommand => $command
                    ->publishMigrations()
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('Eng-MMustafa/automata-kit'),
            )
            ->hasRoute('web')
            ->hasConfigFile()
            ->discoversMigrations();
    }

    /**
     * Registering services.
     */
    public function registeringPackage(): void
    {
        $this->app->singleton(
            AutomationManager::class,
            fn ($app): \AutomataKit\LaravelAutomationConnect\Services\AutomationManager => new AutomationManager($app),
        );

        $this->app->alias(AutomationManager::class, 'automation');

        $this->registerDefaultDrivers();
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
