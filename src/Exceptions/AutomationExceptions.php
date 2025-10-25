<?php

namespace AutomataKit\LaravelAutomationConnect\Exceptions;

use Exception;

class AutomationException extends Exception
{
    //
}

class DriverNotFoundException extends AutomationException
{
    public function __construct(string $driver)
    {
        parent::__construct("Automation driver '{$driver}' not found.");
    }
}

class WebhookVerificationException extends AutomationException
{
    public function __construct(string $service)
    {
        parent::__construct("Webhook verification failed for service '{$service}'.");
    }
}

class ConfigurationException extends AutomationException
{
    public function __construct(string $driver, string $missingConfig)
    {
        parent::__construct("Missing configuration '{$missingConfig}' for driver '{$driver}'.");
    }
}

class RateLimitException extends AutomationException
{
    public function __construct(string $driver, int $limit)
    {
        parent::__construct("Rate limit exceeded for driver '{$driver}'. Limit: {$limit} requests per minute.");
    }
}
