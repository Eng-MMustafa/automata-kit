<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Exceptions;

use Exception;

final class DriverNotFoundException extends Exception
{
    public static function make(string $driver): self
    {
        return new self("Automation driver '{$driver}' not found.");
    }
}
