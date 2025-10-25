<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Exceptions;

use Exception;

final class RateLimitException extends Exception
{
    public static function make(string $driver, int $limit): self
    {
        return new self("Rate limit exceeded for driver '{$driver}'. Limit: {$limit} requests per minute.");
    }
}
