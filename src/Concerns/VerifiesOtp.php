<?php

namespace Horlerdipo\SimpleOtp\Concerns;

use Horlerdipo\SimpleOtp\Models\Otp as OtpModel;
use Illuminate\Support\Facades\Hash;

trait VerifiesOtp
{
    /**
     * @return array{status: bool, message: string}
     */
    public function verifyOtp(string $destination, string $token, string $purpose, bool $use = true): array
    {

        $otpRecord = OtpModel::query()
            ->where('destination', $destination)
            ->where('purpose', $purpose)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $otpRecord) {
            return [
                'status' => false,
                'message' => 'This OTP is incorrect',
            ];
        }

        if ($otpRecord->is_hashed) {
            $isTokenCorrect = Hash::check($token, $otpRecord->token);
            if (! $isTokenCorrect) {
                return [
                    'status' => false,
                    'message' => 'This OTP is incorrect',
                ];
            }
        }

        if ($otpRecord->token !== $token) {
            return [
                'status' => false,
                'message' => 'This OTP is incorrect',
            ];
        }

        if ($otpRecord->is_used) {
            return [
                'status' => false,
                'message' => 'This OTP has already been used',
            ];
        }

        if ($otpRecord->expires_at <= now()) {
            return [
                'status' => false,
                'message' => 'This OTP has expired',
            ];
        }

        if ($use) {
            $otpRecord->update(['is_used' => true]);
        }

        return [
            'status' => true,
            'message' => 'This OTP is valid',
        ];

    }
}
