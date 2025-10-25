<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Exceptions;

use Exception;

final class WebhookVerificationException extends Exception
{
    public static function make(string $service): self
    {
        return new self("Webhook verification failed for service '{$service}'.");
    }
}
