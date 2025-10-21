<?php

use AutomataKit\LaravelAutomationConnect\Tests\TestCase;
use AutomataKit\LaravelAutomationConnect\Facades\Automation;

class SlackDriverTest extends TestCase
{
    /** @test */
    public function it_can_send_slack_messages(): void
    {
        $this->app['config']->set('automation.drivers.slack', [
            'webhook_url' => 'https://hooks.slack.com/test',
        ]);

        $response = Automation::to('slack')->send([
            'text' => 'Test message',
            'channel' => '#test'
        ]);

        $this->assertNotNull($response);
    }

    /** @test */  
    public function it_can_handle_slack_webhooks(): void
    {
        $payload = [
            'token' => 'verification_token',
            'challenge' => 'test_challenge',
            'type' => 'url_verification'
        ];

        $response = $this->postJson('/webhooks/slack', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'service' => 'slack'
            ]);
    }
}

class AutomationManagerTest extends TestCase  
{
    /** @test */
    public function it_can_get_available_drivers(): void
    {
        $drivers = Automation::getAvailableDrivers();

        $this->assertContains('slack', $drivers);
        $this->assertContains('n8n', $drivers);
        $this->assertContains('telegram', $drivers);
    }

    /** @test */
    public function it_can_check_if_driver_exists(): void
    {
        $this->assertTrue(Automation::hasDriver('slack'));
        $this->assertFalse(Automation::hasDriver('nonexistent'));
    }
}

class WebhookLogTest extends TestCase
{
    /** @test */
    public function it_logs_webhook_requests(): void
    {
        $payload = ['test' => 'data'];

        $response = $this->postJson('/webhooks/slack', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('automation_webhook_logs', [
            'service' => 'slack',
            'status' => 'success'
        ]);
    }

    /** @test */
    public function it_calculates_success_rate(): void
    {
        // Create test data
        \AutomataKit\LaravelAutomationConnect\Models\WebhookLog::create([
            'service' => 'slack',
            'status' => 'success',
            'payload' => [],
        ]);

        \AutomataKit\LaravelAutomationConnect\Models\WebhookLog::create([
            'service' => 'slack', 
            'status' => 'failed',
            'payload' => [],
        ]);

        $rate = \AutomataKit\LaravelAutomationConnect\Models\WebhookLog::getSuccessRate('slack');

        $this->assertEquals(50.0, $rate);
    }
}