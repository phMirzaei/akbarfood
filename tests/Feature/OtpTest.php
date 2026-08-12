<?php

namespace Tests\Feature;

use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_blocked_after_three_wrong_otp_attempts(): void
    {
        $otp = Otp::create([
            'phone' => '09123456789',
            'code' => bcrypt('1234'),
            'attempts' => 0,
            'expired_at' => now()->addMinutes(10),
            'next_allowed_request_otp' => now(),
        ]);

        $this->postJson('/api/auth/verify-otp', [
            'phone' => '09123456789',
            'code' => '1111',
        ]);

        $this->postJson('/api/auth/verify-otp', [
            'phone' => '09123456789',
            'code' => '1111',
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '09123456789',
            'code' => '1111',
        ]);

        $response->assertStatus(429);

        $this->assertNotNull(
            $otp->fresh()->blocked_until
        );
        $this->assertSame(
            4,
            $otp->fresh()->attempts
        );
    }
}
