<?php

namespace Horlerdipo\SimpleOtp\Concerns;

use Horlerdipo\SimpleOtp\Models\Otp as OtpModel;
use Illuminate\Support\Facades\Hash;

trait StoresOtp
{
    protected function storeOtp(string $destination, string $token, string $purpose, int $expiration, bool $hashToken): void
    {
        OtpModel::query()->updateOrCreate([
            'destination' => $destination,
            'destination_type' => $this->channel(),
            'purpose' => $purpose,
        ], [
            'expires_at' => now()->addMinutes($expiration),
            'is_used' => false,
            'is_hashed' => $hashToken,
            'token' => $hashToken ? Hash::make($token) : $token,
        ]);
    }

    abstract protected function channel(): string;
}
