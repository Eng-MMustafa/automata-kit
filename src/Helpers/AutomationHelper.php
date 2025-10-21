<?php

namespace AutomataKit\LaravelAutomationConnect\Helpers;

class AutomationHelper
{
    /**
     * Format message for different platforms.
     */
    public static function formatMessage(string $message, string $platform, array $options = []): array
    {
        return match ($platform) {
            'slack' => self::formatSlackMessage($message, $options),
            'discord' => self::formatDiscordMessage($message, $options),
            'telegram' => self::formatTelegramMessage($message, $options),
            default => ['text' => $message]
        };
    }

    /**
     * Format Slack message with rich formatting.
     */
    public static function formatSlackMessage(string $message, array $options = []): array
    {
        $formatted = ['text' => $message];

        if (isset($options['channel'])) {
            $formatted['channel'] = $options['channel'];
        }

        if (isset($options['username'])) {
            $formatted['username'] = $options['username'];
        }

        if (isset($options['emoji'])) {
            $formatted['icon_emoji'] = $options['emoji'];
        }

        return $formatted;
    }

    /**
     * Format Discord message.
     */
    public static function formatDiscordMessage(string $message, array $options = []): array
    {
        return [
            'content' => $message,
            'username' => $options['username'] ?? 'Laravel Bot',
            'avatar_url' => $options['avatar'] ?? null,
        ];
    }

    /**
     * Format Telegram message with HTML/Markdown.
     */
    public static function formatTelegramMessage(string $message, array $options = []): array
    {
        return [
            'text' => $message,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
            'disable_web_page_preview' => $options['disable_preview'] ?? false,
        ];
    }

    /**
     * Sanitize webhook payload.
     */
    public static function sanitizePayload(array $payload): array
    {
        // Remove sensitive data
        $sensitive = ['password', 'token', 'secret', 'key', 'auth'];
        
        return self::recursiveFilter($payload, $sensitive);
    }

    /**
     * Recursively filter sensitive data from arrays.
     */
    private static function recursiveFilter(array $array, array $sensitiveKeys): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::recursiveFilter($value, $sensitiveKeys);
            } elseif (is_string($key) && self::containsSensitiveKey($key, $sensitiveKeys)) {
                $array[$key] = '[REDACTED]';
            }
        }

        return $array;
    }

    /**
     * Check if key contains sensitive information.
     */
    private static function containsSensitiveKey(string $key, array $sensitiveKeys): bool
    {
        $key = strtolower($key);
        
        foreach ($sensitiveKeys as $sensitive) {
            if (str_contains($key, strtolower($sensitive))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate webhook signature.
     */
    public static function validateSignature(string $payload, string $signature, string $secret, string $algorithm = 'sha256'): bool
    {
        $expectedSignature = hash_hmac($algorithm, $payload, $secret);
        
        return hash_equals($signature, $expectedSignature) ||
               hash_equals($signature, "{$algorithm}={$expectedSignature}");
    }

    /**
     * Generate webhook URL for service.
     */
    public static function generateWebhookUrl(string $service, ?string $event = null): string
    {
        $baseUrl = config('app.url');
        $prefix = config('automation.webhook_prefix', 'webhooks');
        
        $url = "{$baseUrl}/{$prefix}/{$service}";
        
        if ($event) {
            $url .= "/{$event}";
        }
        
        return $url;
    }

    /**
     * Convert data to API-specific format.
     */
    public static function transformData(array $data, string $targetFormat): array
    {
        return match ($targetFormat) {
            'hubspot' => self::transformToHubSpot($data),
            'airtable' => self::transformToAirtable($data),
            'sheets' => self::transformToGoogleSheets($data),
            default => $data
        };
    }

    /**
     * Transform data for HubSpot CRM format.
     */
    private static function transformToHubSpot(array $data): array
    {
        $hubspotData = [];
        
        foreach ($data as $key => $value) {
            // Convert common field names
            $hubspotKey = match ($key) {
                'name', 'full_name' => 'firstname',
                'surname', 'last_name' => 'lastname',
                'phone_number', 'mobile' => 'phone',
                'company_name' => 'company',
                default => strtolower($key)
            };
            
            $hubspotData[$hubspotKey] = $value;
        }
        
        return $hubspotData;
    }

    /**
     * Transform data for Airtable format.
     */
    private static function transformToAirtable(array $data): array
    {
        return array_map(function ($key, $value) {
            // Airtable field names should be Title Case
            return [ucwords(str_replace('_', ' ', $key)) => $value];
        }, array_keys($data), $data);
    }

    /**
     * Transform data for Google Sheets format.
     */
    private static function transformToGoogleSheets(array $data): array
    {
        // Convert to array of values for row insertion
        return array_values($data);
    }

    /**
     * Rate limiting check.
     */
    public static function checkRateLimit(string $driver, string $identifier): bool
    {
        if (!config('automation.rate_limiting.enabled', true)) {
            return true;
        }

        $limit = config("automation.rate_limiting.driver_limits.{$driver}") 
               ?? config('automation.rate_limiting.default_limit', 60);

        $key = "automation:rate_limit:{$driver}:{$identifier}";
        
        return app('cache')->throttle($key, $limit, now()->addMinute());
    }
}