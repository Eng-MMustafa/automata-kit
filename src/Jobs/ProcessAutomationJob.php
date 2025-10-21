<?php

namespace AutomataKit\LaravelAutomationConnect\Jobs;

use AutomataKit\LaravelAutomationConnect\Services\AutomationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAutomationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $driver,
        protected array $data,
        protected array $options = []
    ) {}

    public function handle(AutomationManager $automation): void
    {
        try {
            $result = $automation->to($this->driver)->send($this->data, $this->options);
            
            Log::info('Automation job completed successfully', [
                'driver' => $this->driver,
                'data_keys' => array_keys($this->data),
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Automation job failed', [
                'driver' => $this->driver,
                'error' => $e->getMessage(),
                'data_keys' => array_keys($this->data),
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Automation job failed permanently', [
            'driver' => $this->driver,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}