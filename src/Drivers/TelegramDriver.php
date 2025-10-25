<?php

declare(strict_types=1);

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;
use InvalidArgumentException;

final class TelegramDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'telegram';
    }

    public function send(array $data, array $options = []): mixed
    {
        $botToken = $this->getConfigValue('bot_token');

        throw_unless(
            $botToken,
            InvalidArgumentException::class,
            'bot_token is required for Telegram',
        );

        $method = $options['method'] ?? 'sendMessage';

        $payload = $this->formatPayload($data, $method);

        return $this->makeRequest('POST', "https://api.telegram.org/bot{$botToken}/{$method}", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
    }

    public function handleWebhook(Request $request): mixed
    {
        $update = $request->all();

        if (isset($update['message'])) {
            return $this->handleMessage($update['message']);
        }

        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
        }

        return ['status' => 'received'];
    }

    public function verifyWebhook(Request $request): bool
    {
        $token = $this->getConfigValue('bot_token');

        // Telegram doesn't use signature verification by default
        // But you can implement IP validation or secret token validation
        return (bool) $token;
    }

    public function getAvailableActions(): array
    {
        return [
            'send' => 'Send message',
            'sendMessage' => 'Send text message',
            'sendPhoto' => 'Send photo',
            'sendDocument' => 'Send document',
            'sendLocation' => 'Send location',
        ];
    }

    public function getSupportedEvents(): array
    {
        return [
            'message',
            'edited_message',
            'callback_query',
            'inline_query',
            'chosen_inline_result',
        ];
    }

    protected function formatPayload(array $data, string $method): array
    {
        return match ($method) {
            'sendMessage' => [
                'chat_id' => $data['chat_id'] ?? $this->getConfigValue('default_chat_id'),
                'text' => $data['text'] ?? $data['message'] ?? 'Message from Laravel',
                'parse_mode' => $data['parse_mode'] ?? 'HTML',
            ],
            default => $data,
        };
    }

    protected function handleMessage(array $message): array
    {
        $this->log('info', 'Telegram message received', [
            'message_id' => $message['message_id'],
            'from' => $message['from']['id'] ?? null,
            'text' => $message['text'] ?? null,
        ]);

        return ['status' => 'message_processed'];
    }

    protected function handleCallbackQuery(array $callbackQuery): array
    {
        $this->log('info', 'Telegram callback query received', [
            'id' => $callbackQuery['id'],
            'data' => $callbackQuery['data'] ?? null,
        ]);

        return ['status' => 'callback_processed'];
    }
}
