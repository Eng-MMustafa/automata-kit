<?php

namespace AutomataKit\LaravelAutomationConnect\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property array $payload
 * @property array $headers
 * @property array $response
 * @property float $processing_time_ms
 * @property Carbon $processed_at
 */
class WebhookLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'automation_webhook_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service',
        'event',
        'payload',
        'headers',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
        'response',
        'processing_time_ms',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'response' => 'array',
            'processing_time_ms' => 'float',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Scope to filter by service.
     *
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function forService(Builder $builder, string $service): Builder
    {
        return $builder->where('service', $service);
    }

    /**
     * Scope to filter by status.
     *
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withStatus(Builder $builder, string $status): Builder
    {
        return $builder->where('status', $status);
    }

    /**
     * Scope to get successful webhooks.
     *
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function successful(Builder $builder): Builder
    {
        return $builder->withStatus('success');
    }

    /**
     * Scope to get failed webhooks.
     *
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function failed(Builder $builder): Builder
    {
        return $builder->withStatus('failed');
    }

    /**
     * Get the webhook success rate for a service.
     */
    public static function getSuccessRate(?string $service = null): float
    {
        $query = static::query()
            ->when($service, fn (Builder $builder, string $service): Builder => $builder->forService($service));

        $total = $query->count();

        if ($total === 0) {
            return 0.0;
        }

        $successful = $query->successful()->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average processing time for a service.
     */
    public static function getAverageProcessingTime(?string $service = null): float
    {
        $processingTime = static::query()
            ->whereNotNull('processing_time_ms')
            ->when($service, fn (Builder $builder, string $service): Builder => $builder->forService($service))
            ->avg('processing_time_ms');

        return round($processingTime, 2);
    }
}
