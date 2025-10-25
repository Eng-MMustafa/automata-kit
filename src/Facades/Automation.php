<?php

namespace AutomataKit\LaravelAutomationConnect\Facades;

use AutomataKit\LaravelAutomationConnect\Services\AutomationManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract to(string $driver)
 * @method static \AutomataKit\LaravelAutomationConnect\Contracts\AutomationConnectorContract driver(string $driver = null)
 * @method static array getAvailableDrivers()
 * @method static bool hasDriver(string $driver)
 *
 * @see \AutomataKit\LaravelAutomationConnect\Services\AutomationManager
 */
class Automation extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return AutomationManager::class;
    }
}
