<?php

namespace AutomataKit\LaravelAutomationConnect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookLog extends Model
{
    use HasFactory;

    protected $table = 'automation_webhook_logs';

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

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'response' => 'array',
        'processing_time_ms' => 'float',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter by service.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $service
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForService($query, string $service)
    {
        return $query->where('service', $service);
    }

    /**
     * Scope to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get successful webhooks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed webhooks.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get the webhook success rate for a service.
     *
     * @param string|null $service
     * @return float
     */
    public static function getSuccessRate(?string $service = null): float
    {
        $query = static::query();
        
        if ($service) {
            $query->forService($service);
        }

        $total = $query->count();
        
        if ($total === 0) {
            return 0.0;
        }

        $successful = $query->successful()->count();
        
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average processing time for a service.
     *
     * @param string|null $service
     * @return float
     */
    public static function getAverageProcessingTime(?string $service = null): float
    {
        $query = static::query()->whereNotNull('processing_time_ms');
        
        if ($service) {
            $query->forService($service);
        }

        return round($query->avg('processing_time_ms') ?? 0, 2);
    }
}