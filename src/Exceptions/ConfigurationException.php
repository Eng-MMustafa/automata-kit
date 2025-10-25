<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Exceptions;

use Exception;

final class ConfigurationException extends Exception
{
    public static function make(string $driver, string $missingConfig): self
    {
        return new self("Missing configuration '{$missingConfig}' for driver '{$driver}'.");
    }
}
