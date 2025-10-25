<?php

namespace AutomataKit\LaravelAutomationConnect\Http\Controllers;

use AutomataKit\LaravelAutomationConnect\Events\WebhookReceived;
use AutomataKit\LaravelAutomationConnect\Models\WebhookLog;
use AutomataKit\LaravelAutomationConnect\Services\AutomationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookController
{
    public function __construct(
        protected AutomationManager $automationManager
    ) {}

    /**
     * Handle incoming webhook requests.
     */
    public function __invoke(Request $request, string $service, ?string $event = null): JsonResponse
    {
        $startTime = microtime(true);
        $webhookLog = null;

        try {
            // Check if the driver exists
            if (! $this->automationManager->hasDriver($service)) {
                return response()->json([
                    'error' => 'Service not supported',
                    'service' => $service,
                ], 404);
            }

            // Get the driver instance
            $driver = $this->automationManager->driver($service);

            // Create webhook log
            if (config('automation.logging.enabled', true)) {
                $webhookLog = WebhookLog::query()->create([
                    'service' => $service,
                    'event' => $event,
                    'payload' => $request->all(),
                    'headers' => $request->headers->all(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'processing',
                ]);
            }

            // Verify webhook signature if supported
            if ($driver->supportsIncomingWebhooks() && ! $driver->verifyWebhook($request)) {
                $this->updateWebhookLog($webhookLog, 'failed', 'Invalid webhook signature');

                return response()->json([
                    'error' => 'Invalid webhook signature',
                ], 401);
            }

            // Handle the webhook
            $response = $driver->handleWebhook($request);

            // Dispatch the webhook event
            event(new WebhookReceived($service, $event, $request->all(), $response));

            // Calculate processing time
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);

            // Update webhook log
            $this->updateWebhookLog($webhookLog, 'success', null, $response, $processingTime);

            return response()->json([
                'success' => true,
                'service' => $service,
                'event' => $event,
                'response' => $response,
                'processing_time_ms' => $processingTime,
            ]);

        } catch (Throwable $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            $errorMessage = $e->getMessage();

            // Log the error
            Log::error('Webhook processing failed', [
                'service' => $service,
                'event' => $event,
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            // Update webhook log
            $this->updateWebhookLog($webhookLog, 'failed', $errorMessage, null, $processingTime);

            return response()->json([
                'error' => 'Webhook processing failed',
                'message' => config('app.debug') ? $errorMessage : 'Internal server error',
                'service' => $service,
                'event' => $event,
                'processing_time_ms' => $processingTime,
            ], 500);
        }
    }

    /**
     * Update webhook log with results.
     */
    protected function updateWebhookLog(
        ?WebhookLog $webhookLog,
        string $status,
        ?string $errorMessage = null,
        mixed $response = null,
        ?float $processingTime = null
    ): void {
        if (! $webhookLog instanceof WebhookLog) {
            return;
        }

        $webhookLog->update([
            'status' => $status,
            'error_message' => $errorMessage,
            'response' => $response,
            'processing_time_ms' => $processingTime,
            'processed_at' => now(),
        ]);
    }
}
