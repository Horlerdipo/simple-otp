<?php

use Carbon\Carbon;
use Horlerdipo\SimpleOtp\Models\Otp;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    // Clear the database before each test
    Otp::query()->delete();
});

it('deletes OTPs that have expired for longer than 24 hours', function () {
    // 1. Expired 30 hours ago (should be deleted with default 24 hours)
    Otp::factory()->create([
        'token' => '123456',
        'destination' => 'test1@example.com',
        'expires_at' => Carbon::now()->subHours(30),
    ]);

    // 2. Expired 25 hours ago (should be deleted with hours=24)
    Otp::factory()->create([
        'token' => '234567',
        'destination' => 'test2@example.com',
        'expires_at' => Carbon::now()->subHours(25),
    ]);

    // 3. Expired 10 hours ago (should NOT be deleted with hours=24)
    Otp::factory()->create([
        'token' => '345678',
        'destination' => 'test3@example.com',
        'expires_at' => Carbon::now()->subHours(10),
    ]);

    // 4. Expired 2 hours ago (should NOT be deleted with hours=24)
    Otp::factory()->create([
        'token' => '456789',
        'destination' => 'test4@example.com',
        'expires_at' => Carbon::now()->subHours(2),
    ]);

    // Verify we have 6 OTPs before pruning
    expect(Otp::count())->toBe(4);

    // Run command with hours=6 parameter
    Artisan::call('simple-otp:prune-expired-otp', ['hours' => 24]);

    // Only OTPs expired more than 24 hours ago should be deleted
    // OTPs #1, #2 should be deleted, leaving #3, #4
    expect(Otp::count())->toBe(2)
        ->and(Otp::pluck('destination')->toArray())->toContain('test3@example.com')
        ->and(Otp::pluck('destination')->toArray())->toContain('test4@example.com')
        ->and(Otp::pluck('destination')->toArray())->not->toContain('test1@example.com')
        ->and(Otp::pluck('destination')->toArray())->not->toContain('test2@example.com');
});

it('handles empty database gracefully', function () {
    // Ensure database is empty
    Otp::query()->delete();

    // Run the command
    $result = Artisan::call('simple-otp:prune-expired-otp');

    // Command should execute without errors
    expect($result)->toBe(0);
});
