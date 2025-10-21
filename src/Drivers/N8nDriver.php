<?php

namespace AutomataKit\LaravelAutomationConnect\Drivers;

use Illuminate\Http\Request;

class N8nDriver extends BaseDriver
{
    /**
     * Get the driver name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'n8n';
    }

    /**
     * Send data to n8n workflow.
     *
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function send(array $data, array $options = []): mixed
    {
        $webhookUrl = $this->getConfigValue('webhook_url') ?? $options['webhook_url'] ?? null;
        
        if (!$webhookUrl) {
            throw new \InvalidArgumentException('webhook_url is required for n8n');
        }

        $method = $options['method'] ?? 'POST';
        $headers = array_merge(
            $this->getDefaultHeaders(),
            $options['headers'] ?? []
        );

        return $this->makeRequest($method, $webhookUrl, [
            'headers' => $headers,
            'json' => $data,
        ]);
    }

    /**
     * Get default headers for n8n requests.
     *
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Laravel-Automation-Connect/1.0',
        ];

        // Add authentication if configured
        $apiKey = $this->getConfigValue('api_key');
        if ($apiKey) {
            $headers['X-N8N-API-KEY'] = $apiKey;
        }

        $basicAuth = $this->getConfigValue('basic_auth');
        if ($basicAuth && isset($basicAuth['username']) && isset($basicAuth['password'])) {
            $headers['Authorization'] = 'Basic ' . base64_encode(
                $basicAuth['username'] . ':' . $basicAuth['password']
            );
        }

        return $headers;
    }

    /**
     * Handle incoming n8n webhook.
     *
     * @param Request $request
     * @return mixed
     */
    public function handleWebhook(Request $request): mixed
    {
        $payload = $request->all();
        
        $this->log('info', 'n8n webhook received', [
            'method' => $request->method(),
            'payload' => $payload,
            'headers' => $request->headers->all(),
        ]);

        // Extract workflow information if available
        $workflowId = $request->header('X-N8N-Workflow-Id') ?? $payload['workflowId'] ?? null;
        $executionId = $request->header('X-N8N-Execution-Id') ?? $payload['executionId'] ?? null;

        return [
            'status' => 'received',
            'workflow_id' => $workflowId,
            'execution_id' => $executionId,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Trigger a specific n8n workflow.
     *
     * @param string $workflowId
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function triggerWorkflow(string $workflowId, array $data = [], array $options = []): mixed
    {
        $baseUrl = $this->getConfigValue('base_url');
        
        if (!$baseUrl) {
            throw new \InvalidArgumentException('base_url is required for workflow triggers');
        }

        $url = rtrim($baseUrl, '/') . "/webhook/{$workflowId}";
        
        return $this->send($data, array_merge($options, ['webhook_url' => $url]));
    }

    /**
     * Execute a workflow via n8n API.
     *
     * @param string $workflowId
     * @param array $data
     * @return mixed
     */
    public function executeWorkflow(string $workflowId, array $data = []): mixed
    {
        $baseUrl = $this->getConfigValue('base_url');
        $apiKey = $this->getConfigValue('api_key');
        
        if (!$baseUrl || !$apiKey) {
            throw new \InvalidArgumentException('base_url and api_key are required for workflow execution');
        }

        $url = rtrim($baseUrl, '/') . "/api/v1/workflows/{$workflowId}/execute";

        return $this->makeRequest('POST', $url, [
            'headers' => [
                'X-N8N-API-KEY' => $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'data' => $data,
            ],
        ]);
    }

    /**
     * Get workflow information from n8n.
     *
     * @param string $workflowId
     * @return mixed
     */
    public function getWorkflow(string $workflowId): mixed
    {
        $baseUrl = $this->getConfigValue('base_url');
        $apiKey = $this->getConfigValue('api_key');
        
        if (!$baseUrl || !$apiKey) {
            throw new \InvalidArgumentException('base_url and api_key are required');
        }

        $url = rtrim($baseUrl, '/') . "/api/v1/workflows/{$workflowId}";

        return $this->makeRequest('GET', $url, [
            'headers' => [
                'X-N8N-API-KEY' => $apiKey,
            ],
        ]);
    }

    /**
     * List available workflows.
     *
     * @return mixed
     */
    public function listWorkflows(): mixed
    {
        $baseUrl = $this->getConfigValue('base_url');
        $apiKey = $this->getConfigValue('api_key');
        
        if (!$baseUrl || !$apiKey) {
            throw new \InvalidArgumentException('base_url and api_key are required');
        }

        $url = rtrim($baseUrl, '/') . '/api/v1/workflows';

        return $this->makeRequest('GET', $url, [
            'headers' => [
                'X-N8N-API-KEY' => $apiKey,
            ],
        ]);
    }

    /**
     * Get available actions for n8n.
     *
     * @return array
     */
    public function getAvailableActions(): array
    {
        return [
            'send' => 'Send data to webhook',
            'trigger_workflow' => 'Trigger specific workflow',
            'execute_workflow' => 'Execute workflow via API',
            'get_workflow' => 'Get workflow information',
            'list_workflows' => 'List all workflows',
        ];
    }

    /**
     * Get supported webhook events for n8n.
     *
     * @return array
     */
    public function getSupportedEvents(): array
    {
        return [
            'webhook',
            'workflow_completed',
            'workflow_failed',
            'workflow_started',
            'execution_finished',
        ];
    }

    /**
     * Check if the driver supports incoming webhooks.
     *
     * @return bool
     */
    public function supportsIncomingWebhooks(): bool
    {
        return true;
    }

    /**
     * Check if the driver supports outgoing actions.
     *
     * @return bool
     */
    public function supportsOutgoingActions(): bool
    {
        return true;
    }
}