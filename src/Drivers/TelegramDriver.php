<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class TelegramDriver extends BaseDriver
{
    public function getName(): string
    {
        return 'telegram';
    }

    public function send(array $data, array $options = []): mixed
    {
        $botToken = $this->getConfigValue('bot_token');
        
        if (!$botToken) {
            throw new \InvalidArgumentException('bot_token is required for Telegram');
        }

        $method = $options['method'] ?? 'sendMessage';
        $url = "https://api.telegram.org/bot{$botToken}/{$method}";

        $payload = $this->formatPayload($data, $method);

        return $this->makeRequest('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
    }

    protected function formatPayload(array $data, string $method): array
    {
        switch ($method) {
            case 'sendMessage':
                return [
                    'chat_id' => $data['chat_id'] ?? $this->getConfigValue('default_chat_id'),
                    'text' => $data['text'] ?? $data['message'] ?? 'Message from Laravel',
                    'parse_mode' => $data['parse_mode'] ?? 'HTML',
                ];
            default:
                return $data;
        }
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

    public function verifyWebhook(Request $request): bool
    {
        $token = $this->getConfigValue('bot_token');
        
        if (!$token) {
            return true;
        }

        // Telegram doesn't use signature verification by default
        // But you can implement IP validation or secret token validation
        return true;
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
}