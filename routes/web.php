<?php

use AutomataKit\LaravelAutomationConnect\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('automation.webhook_prefix', 'webhooks'))
    ->middleware(config('automation.webhook_middleware', []))
    ->group(function (): void {
        Route::post('/{service}/{event?}', WebhookController::class)->name('automation.webhook');
    });
