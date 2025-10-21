<?php

namespace AutomataKit\LaravelAutomationConnect\Services;

use AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract;
use Illuminate\Container\Container;
use Illuminate\Support\Manager;

class AutomationManager extends Manager
{
    /**
     * Create a new automation manager instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->container['config']['automation.default'];
    }

    /**
     * Create a Slack driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createSlackDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.slack')->setConfig($config);
    }

    /**
     * Create an N8n driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createN8nDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.n8n')->setConfig($config);
    }

    /**
     * Create a Zapier driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createZapierDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.zapier')->setConfig($config);
    }

    /**
     * Create a Make driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createMakeDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.make')->setConfig($config);
    }

    /**
     * Create a Telegram driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createTelegramDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.telegram')->setConfig($config);
    }

    /**
     * Create a WhatsApp driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createWhatsappDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.whatsapp')->setConfig($config);
    }

    /**
     * Create a Google Sheets driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createGoogleSheetsDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.google_sheets')->setConfig($config);
    }

    /**
     * Create an Airtable driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createAirtableDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.airtable')->setConfig($config);
    }

    /**
     * Create a Discord driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createDiscordDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.discord')->setConfig($config);
    }

    /**
     * Create a HubSpot driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createHubspotDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.hubspot')->setConfig($config);
    }

    /**
     * Create a Google Drive driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createGoogleDriveDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.google_drive')->setConfig($config);
    }

    /**
     * Create an OpenAI driver instance.
     *
     * @param array $config
     * @return AutomationConnectorContract
     */
    public function createOpenaiDriver(array $config): AutomationConnectorContract
    {
        return $this->container->make('automation.driver.openai')->setConfig($config);
    }

    /**
     * Send data using the specified driver.
     *
     * @param string $driver
     * @return AutomationConnectorContract
     */
    public function to(string $driver): AutomationConnectorContract
    {
        return $this->driver($driver);
    }

    /**
     * Get all available drivers.
     *
     * @return array
     */
    public function getAvailableDrivers(): array
    {
        return array_keys(config('automation.drivers', []));
    }

    /**
     * Check if a driver is available.
     *
     * @param string $driver
     * @return bool
     */
    public function hasDriver(string $driver): bool
    {
        return in_array($driver, $this->getAvailableDrivers());
    }
}