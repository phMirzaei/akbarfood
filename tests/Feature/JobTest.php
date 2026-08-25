<?php

namespace Tests\Feature;

use App\Jobs\SendNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_notification_is_queued(): void
    {
        Queue::fake();
        $phone = '09140026355';
        $response = $this->postJson('/api/auth/request-otp', [
            'phone' => $phone,
            'name' => 'فاطمه',
        ]);
        $response->assertStatus(200);
        Queue::assertPushed(SendNotification::class);
    }
}
