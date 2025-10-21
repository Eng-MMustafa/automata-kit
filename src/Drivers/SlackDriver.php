<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class SlackDriver extends BaseDriver
{
    /**
     * Get the driver name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'slack';
    }

    /**
     * Send data to Slack.
     *
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function send(array $data, array $options = []): mixed
    {
        $webhookUrl = $this->getConfigValue('webhook_url');
        $token = $this->getConfigValue('bot_token');

        if ($webhookUrl) {
            return $this->sendViaWebhook($data, $webhookUrl);
        } elseif ($token) {
            return $this->sendViaApi($data, $token, $options);
        }

        throw new \InvalidArgumentException('Either webhook_url or bot_token must be configured for Slack');
    }

    /**
     * Send message via Slack webhook.
     *
     * @param array $data
     * @param string $webhookUrl
     * @return mixed
     */
    protected function sendViaWebhook(array $data, string $webhookUrl): mixed
    {
        $payload = $this->formatWebhookPayload($data);

        return $this->makeRequest('POST', $webhookUrl, [
            'json' => $payload,
        ]);
    }

    /**
     * Send message via Slack API.
     *
     * @param array $data
     * @param string $token
     * @param array $options
     * @return mixed
     */
    protected function sendViaApi(array $data, string $token, array $options): mixed
    {
        $endpoint = $options['endpoint'] ?? 'chat.postMessage';
        $url = "https://slack.com/api/{$endpoint}";

        $payload = $this->formatApiPayload($data, $options);

        return $this->makeRequest('POST', $url, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);
    }

    /**
     * Format payload for webhook.
     *
     * @param array $data
     * @return array
     */
    protected function formatWebhookPayload(array $data): array
    {
        // If text is provided directly
        if (isset($data['text'])) {
            return $data;
        }

        // If message is provided
        if (isset($data['message'])) {
            return ['text' => $data['message']];
        }

        // Default formatting
        return [
            'text' => $data['text'] ?? 'Message from Laravel',
            'blocks' => $data['blocks'] ?? null,
            'attachments' => $data['attachments'] ?? null,
        ];
    }

    /**
     * Format payload for API.
     *
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function formatApiPayload(array $data, array $options): array
    {
        return array_merge([
            'channel' => $data['channel'] ?? $this->getConfigValue('default_channel', '#general'),
            'text' => $data['text'] ?? $data['message'] ?? 'Message from Laravel',
        ], $data, $options);
    }

    /**
     * Handle incoming Slack webhook.
     *
     * @param Request $request
     * @return mixed
     */
    public function handleWebhook(Request $request): mixed
    {
        $payload = $request->all();

        // Handle URL verification challenge
        if (isset($payload['challenge'])) {
            return ['challenge' => $payload['challenge']];
        }

        // Handle different event types
        if (isset($payload['event'])) {
            return $this->handleEvent($payload['event'], $payload);
        }

        // Handle slash commands
        if (isset($payload['command'])) {
            return $this->handleSlashCommand($payload);
        }

        // Handle interactive components
        if (isset($payload['payload'])) {
            $interactivePayload = json_decode($payload['payload'], true);
            return $this->handleInteractiveComponent($interactivePayload);
        }

        $this->log('info', 'Slack webhook received', ['payload' => $payload]);

        return ['status' => 'received'];
    }

    /**
     * Handle Slack events.
     *
     * @param array $event
     * @param array $fullPayload
     * @return array
     */
    protected function handleEvent(array $event, array $fullPayload): array
    {
        $this->log('info', 'Slack event received', [
            'event_type' => $event['type'] ?? 'unknown',
            'event' => $event,
        ]);

        return ['status' => 'event_processed'];
    }

    /**
     * Handle Slack slash commands.
     *
     * @param array $payload
     * @return array
     */
    protected function handleSlashCommand(array $payload): array
    {
        $this->log('info', 'Slack slash command received', [
            'command' => $payload['command'],
            'text' => $payload['text'] ?? '',
            'user_id' => $payload['user_id'] ?? null,
        ]);

        return [
            'response_type' => 'ephemeral',
            'text' => 'Command received and processed!',
        ];
    }

    /**
     * Handle Slack interactive components.
     *
     * @param array $payload
     * @return array
     */
    protected function handleInteractiveComponent(array $payload): array
    {
        $this->log('info', 'Slack interactive component received', [
            'type' => $payload['type'] ?? 'unknown',
            'callback_id' => $payload['callback_id'] ?? null,
        ]);

        return ['status' => 'interaction_processed'];
    }

    /**
     * Verify Slack webhook signature.
     *
     * @param Request $request
     * @return bool
     */
    public function verifyWebhook(Request $request): bool
    {
        $signingSecret = $this->getConfigValue('signing_secret');
        
        if (!$signingSecret) {
            return true;
        }

        $signature = $request->header('X-Slack-Signature');
        $timestamp = $request->header('X-Slack-Request-Timestamp');
        
        if (!$signature || !$timestamp) {
            return false;
        }

        // Prevent replay attacks
        if (abs(time() - $timestamp) > 60 * 5) {
            return false;
        }

        $baseString = 'v0:' . $timestamp . ':' . $request->getContent();
        $expectedSignature = 'v0=' . hash_hmac('sha256', $baseString, $signingSecret);

        return hash_equals($signature, $expectedSignature);
    }

    /**
     * Get available actions for Slack.
     *
     * @return array
     */
    public function getAvailableActions(): array
    {
        return [
            'send' => 'Send message to Slack',
            'post_message' => 'Post message to channel',
            'upload_file' => 'Upload file to channel',
            'create_channel' => 'Create new channel',
            'invite_user' => 'Invite user to channel',
        ];
    }

    /**
     * Get supported webhook events for Slack.
     *
     * @return array
     */
    public function getSupportedEvents(): array
    {
        return [
            'message',
            'app_mention',
            'channel_created',
            'channel_deleted',
            'member_joined_channel',
            'member_left_channel',
            'reaction_added',
            'reaction_removed',
            'file_shared',
            'slash_command',
            'interactive_component',
        ];
    }
}